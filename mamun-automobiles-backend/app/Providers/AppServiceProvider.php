<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
        
        \App\Models\Part::observe(\App\Observers\PartObserver::class);

        // Listen for Authentication events
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            \App\Services\ActivityLogService::log('Auth', 'login', "User {$event->user->email} logged in successfully.", null, null, 'info');
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            $email = $event->credentials['email'] ?? 'unknown';
            \App\Services\ActivityLogService::log('Auth', 'failed_login', "Failed login attempt for {$email}.", null, null, 'danger');
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if ($event->user) {
                \App\Services\ActivityLogService::log('Auth', 'logout', "User {$event->user->email} logged out.", null, null, 'info');
            }
        });
    }
}
