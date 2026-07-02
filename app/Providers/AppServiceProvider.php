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
            
            // Jika pengguna adalah Mahasiswa, arahkan link ke aplikasi mobile via Deep Linking
            if ($notifiable instanceof \App\Models\Pengguna && $notifiable->role === \App\Enums\RolePengguna::Mahasiswa) {
                $mobileUrl = env('MOBILE_APP_URL', 'exp://127.0.0.1:8081/--');
                return $mobileUrl . "/reset-password?token={$token}&email={$email}";
            }

            // Jika admin/dosen, arahkan ke frontend website
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return $frontendUrl . "/reset-password?token={$token}&email={$email}";
        });
    }
}
