<?php

namespace App\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        // Render 等平台在反向代理後終止 TLS，Laravel 需強制 https 避免混合內容
        if (env('APP_ENV') === 'production') {
            $url->forceScheme('https');
        }
    }
}
