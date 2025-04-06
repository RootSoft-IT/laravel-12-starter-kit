# laravel-12-starter-kit
A Laravel 12 QuickStart Starter Kit

## ✨ Features

-   🚀 Built on Laravel 12
-   🔐 API Authentication with Laravel Passport (Password Grant)
-   🛠️ Streamlined setup process
-   📦 Optimized project structure
-   🧪 Frontend-agnostic (works with any frontend framework)
-   📱 Postman-ready API collection included

## 📋 Requirements

-   PHP 8.2+
-   Composer
-   MySQL/PostgreSQL
-   Laravel CLI

## ⚙️ Installation

### Option 1: Automated Setup (Recommended)

```bash
# Clone the repository
git clone https://github.com/RootSoft-IT/laravel-12-starter-kit.git
cd laravel-12-starter-kit

# Install dependencies
composer install

# Run the automated setup script
php artisan app:init
```

### Option 2: Manual Setup

```bash
# Clone the repository
git clone https://github.com/RootSoft-IT/laravel-12-starter-kit.git
cd laravel-12-starter-kit

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Configure your database in .env then run migrations
php artisan migrate

# Set up Passport
php artisan passport:client --password
```

## 🚀 Getting Started

After installation, you can:

-   Run the development server: `php artisan serve`
-   Access the API documentation at `/api/documentation`
-   Use the included Postman collection to test endpoints

## 📖 Documentation

Full documentation is available in the `/docs` directory or at our [documentation site](https://your-username.github.io/laravel-12-starter-kit/).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
