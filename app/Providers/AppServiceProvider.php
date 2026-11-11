<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;

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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Automatically create the storage symlink if it doesn't exist on the server
        if (!file_exists(public_path('storage'))) {
            app('files')->link(
                storage_path('app/public'),
                public_path('storage')
            );
        }
    }
}
