<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class AppInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize the application with a complete setup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Laravel 12 Starter Kit initialization...');

        // Step 1: Check for .env file and create if it doesn't exist
        if (!File::exists(base_path('.env'))) {
            $this->info('Creating .env file...');
            File::copy(base_path('.env.example'), base_path('.env'));
            $this->info('.env file created successfully.');
        }

        // Step 2: Generate application key
        $this->info('Generating application key...');
        Artisan::call('key:generate');
        $this->info('Application key generated successfully.');

        // Step 3: Set up database
        $this->setupDatabase();

        // Step 4: Run migrations
        $this->info('Running database migrations...');
        $migrateOutput = Artisan::call('migrate');
        $this->info('Database migrations completed.');

        // Step 5: Set up Passport
        $this->setupPassport();

        // Step 6: Clear cache and optimize
        $this->info('Optimizing application...');
        Artisan::call('optimize:clear');
        $this->info('Application optimized successfully.');

        $this->newLine();
        $this->info('✅ Laravel 12 Starter Kit has been initialized successfully!');
        $this->info('🚀 Run "php artisan serve" to start the development server.');
    }

    /**
     * Set up the database connection.
     */
    private function setupDatabase()
    {
        $this->info('Setting up MySQL database connection...');

        // Check if the user wants to configure the database now
        if ($this->confirm('Do you want to configure the MySQL database connection now?', true)) {
            // Update .env file with MySQL database settings
            $dbHost = $this->ask('Database host?', '127.0.0.1');
            $dbPort = $this->ask('Database port?', '3306');
            $dbName = $this->ask('Database name?', 'laravel');
            $dbUser = $this->ask('Database username?', 'root');
            $dbPass = $this->secret('Database password?') ?: '';

            $this->updateEnvVariable('DB_CONNECTION', 'mysql');
            $this->updateEnvVariable('DB_HOST', $dbHost);
            $this->updateEnvVariable('DB_PORT', $dbPort);
            $this->updateEnvVariable('DB_DATABASE', $dbName);
            $this->updateEnvVariable('DB_USERNAME', $dbUser);
            $this->updateEnvVariable('DB_PASSWORD', $dbPass);

            // Test database connection
            try {
                // Force-reload the configuration
                $config = [
                    'driver' => 'mysql',
                    'host' => $dbHost,
                    'port' => $dbPort,
                    'database' => $dbName,
                    'username' => $dbUser,
                    'password' => $dbPass,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => true,
                    'engine' => null,
                ];

                config(['database.connections.mysql_test' => $config]);

                // Test connection with a simple query
                DB::connection('mysql_test')->select('SELECT 1');
                $this->info('Database connection successful.');
            } catch (\Exception $e) {
                $this->error('Could not connect to the database.');
                $this->error($e->getMessage());

                if ($this->confirm('Would you like to try again?', true)) {
                    $this->setupDatabase();
                }
            }
        } else {
            $this->warn('Skipping database configuration. Make sure to configure your .env file manually.');
        }
    }

    /**
     * Set up Passport Password Grant Client.
     */
    private function setupPassport()
    {
        $this->info('Setting up Passport Password Grant Client...');

        try {
            // Create Password Client
            $this->info('Creating Password Grant Client...');
            $clientOutput = Artisan::call('passport:client', [
                '--password' => true,
                '--name' => 'Laravel Password Grant Client',
                '--no-interaction' => true
            ]);
            $this->info('Password Grant Client created successfully.');

            // Display the client details
            $this->info('Your Passport client credentials:');
            $this->info(Artisan::output());
        } catch (\Exception $e) {
            $this->error('Failed to create Passport client.');
            $this->error($e->getMessage());
        }
    }

    /**
     * Update a variable in the .env file.
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    private function updateEnvVariable($key, $value)
    {
        $path = base_path('.env');

        if (File::exists($path)) {
            $content = File::get($path);

            // If the key exists, replace it
            if (strpos($content, $key . '=') !== false) {
                $content = preg_replace('/^' . $key . '=.*$/m', $key . '=' . $value, $content);
            } else {
                // Otherwise, append it
                $content .= "\n" . $key . '=' . $value;
            }

            File::put($path, $content);
        }
    }
}
