<?php

namespace GenialDigitalNusantara\LaragitVersion;

use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * When set to false, the service provider is loaded immediately during
     * application bootstrap. Set to true if you want to defer loading until
     * the service is actually needed.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Console commands to be instantiated.
     *
     * This array allows you to register custom Artisan commands
     * that come with the package. Currently empty, but can be
     * expanded to include package-specific commands.
     *
     * @var array
     */
    protected $commandList = [
        //
    ];

    /**
     * Get the configuration file path for publishing.
     *
     * Determines the target path for the configuration file when it is published.
     * Supports both Laravel's config_path() helper and a fallback for other contexts.
     *
     * @return string Full path to the published configuration file
     */
    protected function getConfigPath(): string
    {
        if (function_exists('config_path')) {
            return config_path('version.php');
        } else {
            return base_path('config/version.php');
        }
    }

    /**
     * Get the original configuration stub path.
     *
     * Provides the path to the package's default configuration file.
     * This is used for merging and publishing configurations.
     *
     * @return string Full path to the original configuration stub
     */
    protected function getConfigStub(): string
    {
        return __DIR__ . '/../config/version.php';
    }

    /**
     * Load and merge package configuration.
     *
     * Merges the package's default configuration with the published
     * configuration, allowing users to override default settings.
     */
    protected function loadConfig(): void
    {
        $this->mergeConfigFrom($this->getConfigStub(), 'version');
    }

    /**
     * Publish the package configuration.
     *
     * Allows users to publish the package's configuration file to
     * their application's config directory using the 'config' tag.
     */
    protected function publishConfiguration(): void
    {
        $this->publishes([
            $this->getConfigStub() => $this->getConfigPath(),
        ], 'config');
    }

    /**
     * Register the Blade directive for version display.
     *
     * Creates a custom Blade directive @laragitVersion that can be used
     * in Blade templates to display the current version with optional formatting.
     */
    protected function registerBladeDirective(): void
    {
        Blade::directive('laragitVersion', function ($format = null) {
            $formatString = $format ? $format : "'" . Constants::DEFAULT_FORMAT . "'";

            return "<?php echo app('gdn-dev.laragit-version')->show($formatString); ?>";
        });
    }

    /**
     * Register the package service in the application container.
     *
     * Binds the LaragitVersion service to the application's service container
     * as a singleton, ensuring only one instance is used throughout the application.
     */
    protected function registerService(): void
    {
        $this->app->singleton('gdn-dev.laragit-version', function () {
            return new LaragitVersion($this->app);
        });
    }

    /**
     * Register an individual command.
     *
     * Helper method to register a single console command with the application.
     *
     * @param string $name Unique identifier for the command
     * @param string $command Fully qualified class name of the command
     */
    protected function registerCommand($name, $command): void
    {
        $this->app->singleton($name, function () use ($command) {
            return new $command();
        });

        $this->commands($name);
    }

    /**
     * Register all package console commands.
     *
     * Iterates through the $commandList and registers each command
     * using the registerCommand method.
     */
    protected function registerCommands(): void
    {
        collect($this->commandList)->each(function ($command, $key) {
            $this->registerCommand($key, $command);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * Called during the booting process of the application. Used to
     * publish configurations and register Blade directives.
     */
    public function boot(): void
    {
        $this->loadConfig();
        $this->publishConfiguration();
        $this->registerBladeDirective();
    }

    /**
     * Register any application services.
     *
     * Called during the registration process of the application. Used to
     * register the package's service in the application container.
     */
    public function register(): void
    {
        $this->loadConfig();
        $this->registerService();
        $this->registerCommands();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['gdn-dev.laragit-version'];
    }
}
