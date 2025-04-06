<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    public function boot(): void
    {
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(now()->addDay()); 
        Passport::refreshTokensExpireIn(now()->addDays(30)); 

        Passport::tokensCan([
            'user' => 'Access user information',
            'admin' => 'Full administrative access',
        ]);
        
        Passport::setDefaultScope([
            'user',
        ]);
    }
} 