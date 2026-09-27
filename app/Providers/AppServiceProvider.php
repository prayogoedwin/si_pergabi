<?php

namespace App\Providers;

use App\Listeners\AdvanceAnggotaAfterEmailVerified;
use App\Models\Anggota;
use App\Observers\AnggotaObserver;
use App\Services\SettingService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
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
        $appUrl = (string) config('app.url');
        if (str_contains($appUrl, '/index.cgi')) {
            URL::forceRootUrl($appUrl);
        }
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Event::listen(Verified::class, AdvanceAnggotaAfterEmailVerified::class);
        Anggota::observe(AnggotaObserver::class);
        $settings = $this->app->make(SettingService::class);
        $settings->applyMailConfig();
        $settings->applyGoogleConfig();
    }
}
