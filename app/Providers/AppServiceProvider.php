<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

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
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            $email = $notifiable->getEmailForPasswordReset();
            
            // Arahkan semua tautan Reset Password ke web frontend
            // Karena email client (seperti Gmail) tidak mengizinkan link custom scheme (lms://) untuk diklik
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return $frontendUrl . "/reset-password?token={$token}&email={$email}";
        });
    }
}
