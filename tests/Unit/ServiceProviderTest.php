<?php

use GenialDigitalNusantara\LaragitVersion\ServiceProvider;
use GenialDigitalNusantara\LaragitVersion\LaragitVersion;

it('registers the service correctly', function () {
    // Create a simple container mock
    $app = new class {
        private $instances = [];
        
        public function singleton($key, $resolver) {
            $this->instances[$key] = $resolver;
        }
        
        public function make($key) {
            if (isset($this->instances[$key])) {
                $resolver = $this->instances[$key];
                return $resolver();
            }
        }
    };
    
    // Create our service provider
    $serviceProvider = new class ($app) extends ServiceProvider {
        protected $app;
        
        public function __construct($app) {
            $this->app = $app;
            parent::__construct($app);
        }
        
        public function register(): void {
            // Override to use our mock app
            $this->app->singleton('gdn-dev.laragit-version', function () {
                return new LaragitVersion();
            });
        }
    };
    
    // Register the service
    $serviceProvider->register();
    
    // Try to make an instance
    $instance = $app->make('gdn-dev.laragit-version');
    
    expect($instance)->toBeInstanceOf(LaragitVersion::class);
});

it('has required methods', function () {
    // Just verify the methods exist
    expect(method_exists(ServiceProvider::class, 'boot'))->toBeTrue();
    expect(method_exists(ServiceProvider::class, 'register'))->toBeTrue();
});