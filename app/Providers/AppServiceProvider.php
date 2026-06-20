<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
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
        App::setLocale(config('app.locale', 'ar'));

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('app_settings')) {
                $mailHost = \App\Models\AppSetting::get(\App\Models\AppSetting::KEY_MAIL_HOST);
                if ($mailHost) {
                    config([
                        'mail.mailers.smtp.host' => $mailHost,
                        'mail.mailers.smtp.port' => \App\Models\AppSetting::get(\App\Models\AppSetting::KEY_MAIL_PORT, 587),
                        'mail.mailers.smtp.encryption' => \App\Models\AppSetting::get(\App\Models\AppSetting::KEY_MAIL_ENCRYPTION, 'tls'),
                        'mail.mailers.smtp.username' => \App\Models\AppSetting::get(\App\Models\AppSetting::KEY_MAIL_USERNAME),
                        'mail.mailers.smtp.password' => \App\Models\AppSetting::get(\App\Models\AppSetting::KEY_MAIL_PASSWORD),
                        'mail.from.address' => \App\Models\AppSetting::get(\App\Models\AppSetting::KEY_MAIL_FROM_ADDRESS),
                        'mail.from.name' => \App\Models\AppSetting::get(\App\Models\AppSetting::KEY_MAIL_FROM_NAME, config('app.name')),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Ignore if the database is not ready
        }
    }
}
