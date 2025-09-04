<?php

namespace GenialDigitalNusantara\LaragitVersion;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Register the Blade directive
        Blade::directive('laragitVersion', function ($format = null) {
            if ($format) {
                return "<?php echo app('gdn-dev.laragit-version')->show({$format}); ?>";
            }

            return "<?php echo app('gdn-dev.laragit-version')->show(); ?>";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register the service
        $this->app->singleton('gdn-dev.laragit-version', function ($app) {
            return new LaragitVersion($app);
        });
    }
}