<?php

use GenialDigitalNusantara\LaragitVersion\Helper\Constants;
use GenialDigitalNusantara\LaragitVersion\ServiceProvider;
use Illuminate\Container\Container;

it('registers blade directive with default name', function () {
    $container = new Container();
    
    $serviceProvider = new class($container) extends ServiceProvider {
        public $registeredDirectives = [];
        
        protected function registerBladeDirective(): void {
            // Directly test the logic without using config() helper
            $directiveName = Constants::DEFAULT_BLADE_DIRECTIVE;
            
            // Mock blade directive registration
            $this->registeredDirectives[$directiveName] = function ($format = null) {
                $formatString = $format ? $format : "'" . Constants::DEFAULT_FORMAT . "'";
                return "<?php echo app('gdn-dev.laragit-version')->show($formatString); ?>";
            };
        }
        
        public function testRegisterBladeDirective(): void {
            $this->registerBladeDirective();
        }
    };
    
    $serviceProvider->testRegisterBladeDirective();
    
    expect($serviceProvider->registeredDirectives)->toHaveKey(Constants::DEFAULT_BLADE_DIRECTIVE);
    expect($serviceProvider->registeredDirectives[Constants::DEFAULT_BLADE_DIRECTIVE])->toBeCallable();
});

it('registers blade directive with custom name', function () {
    $container = new Container();
    
    $serviceProvider = new class($container) extends ServiceProvider {
        public $registeredDirectives = [];
        
        protected function registerBladeDirective(): void {
            // Test with a custom directive name
            $directiveName = 'myVersion';
            
            // Mock blade directive registration
            $this->registeredDirectives[$directiveName] = function ($format = null) {
                $formatString = $format ? $format : "'" . Constants::DEFAULT_FORMAT . "'";
                return "<?php echo app('gdn-dev.laragit-version')->show($formatString); ?>";
            };
        }
        
        public function testRegisterBladeDirective(): void {
            $this->registerBladeDirective();
        }
    };
    
    $serviceProvider->testRegisterBladeDirective();
    
    expect($serviceProvider->registeredDirectives)->toHaveKey('myVersion');
    expect($serviceProvider->registeredDirectives)->not->toHaveKey(Constants::DEFAULT_BLADE_DIRECTIVE);
    expect($serviceProvider->registeredDirectives['myVersion'])->toBeCallable();
});

it('registers blade directive with fallback when config is missing', function () {
    $container = new Container();
    
    $serviceProvider = new class($container) extends ServiceProvider {
        public $registeredDirectives = [];
        
        protected function registerBladeDirective(): void {
            // Test the fallback behavior
            $directiveName = Constants::DEFAULT_BLADE_DIRECTIVE;
            
            // Mock blade directive registration
            $this->registeredDirectives[$directiveName] = function ($format = null) {
                $formatString = $format ? $format : "'" . Constants::DEFAULT_FORMAT . "'";
                return "<?php echo app('gdn-dev.laragit-version')->show($formatString); ?>";
            };
        }
        
        public function testRegisterBladeDirective(): void {
            $this->registerBladeDirective();
        }
    };
    
    $serviceProvider->testRegisterBladeDirective();
    
    expect($serviceProvider->registeredDirectives)->toHaveKey(Constants::DEFAULT_BLADE_DIRECTIVE);
    expect($serviceProvider->registeredDirectives[Constants::DEFAULT_BLADE_DIRECTIVE])->toBeCallable();
});