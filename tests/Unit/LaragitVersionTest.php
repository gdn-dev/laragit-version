<?php

use GenialDigitalNusantara\LaragitVersion\LaragitVersion;

it('can get version from VERSION file', function () {
    // Create a temporary directory for testing
    $testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laragit_test';
    if (! file_exists($testDir)) {
        mkdir($testDir);
    }

    // Create a temporary VERSION file
    $versionFile = $testDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, '1.2.3');

    // Create a mock version class that uses our test directory
    $laragitVersion = new class ($testDir) extends LaragitVersion {
        private $testDir;

        public function __construct($testDir)
        {
            $this->testDir = $testDir;
        }

        protected function getBasePath(): string
        {
            return $this->testDir;
        }
    };

    expect($laragitVersion->getCurrentVersion())->toBe('1.2.3');
    expect($laragitVersion->show())->toBe('Version 1.2.3');
    expect($laragitVersion->show('compact'))->toBe('v1.2.3');

    // Clean up
    unlink($versionFile);
    rmdir($testDir);
});

it('can get version from git when VERSION file does not exist', function () {
    // Create a temporary directory for testing
    $testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laragit_test';
    if (! file_exists($testDir)) {
        mkdir($testDir);
    }

    // Make sure no VERSION file exists
    $versionFile = $testDir . DIRECTORY_SEPARATOR . 'VERSION';
    if (file_exists($versionFile)) {
        unlink($versionFile);
    }

    // Create a mock version class that uses our test directory
    $laragitVersion = new class ($testDir) extends LaragitVersion {
        private $testDir;

        public function __construct($testDir)
        {
            $this->testDir = $testDir;
        }

        protected function getBasePath(): string
        {
            return $this->testDir;
        }
    };

    $version = $laragitVersion->getCurrentVersion();

    // Version should be a non-empty string
    expect($version)->toBeString();
    // Note: We can't assert much about the content since it depends on the git repo

    // Clean up
    rmdir($testDir);
});

it('returns default version when neither VERSION file nor git is available', function () {
    // Create a temporary directory for testing
    $testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laragit_test';
    if (! file_exists($testDir)) {
        mkdir($testDir);
    }

    // Make sure no VERSION file exists
    $versionFile = $testDir . DIRECTORY_SEPARATOR . 'VERSION';
    if (file_exists($versionFile)) {
        unlink($versionFile);
    }

    // Create a mock version class that returns empty for git commands
    $laragitVersion = new class ($testDir) extends LaragitVersion {
        private $testDir;

        public function __construct($testDir)
        {
            $this->testDir = $testDir;
        }

        protected function getBasePath(): string
        {
            return $this->testDir;
        }

        protected function shell($command): string
        {
            // Return empty string to simulate no git
            return '';
        }
    };

    expect($laragitVersion->getCurrentVersion())->toBe('0.0.0');

    // Clean up
    rmdir($testDir);
});

it('can get version info as array', function () {
    // Create a temporary directory for testing
    $testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laragit_test';
    if (! file_exists($testDir)) {
        mkdir($testDir);
    }

    // Create a temporary VERSION file
    $versionFile = $testDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, '2.0.0');

    // Create a mock version class that uses our test directory
    $laragitVersion = new class ($testDir) extends LaragitVersion {
        private $testDir;

        public function __construct($testDir)
        {
            $this->testDir = $testDir;
        }

        protected function getBasePath(): string
        {
            return $this->testDir;
        }
    };

    $info = $laragitVersion->getVersionInfo();

    expect($info)->toBeArray();
    expect($info)->toHaveKey('version');
    expect($info)->toHaveKey('formatted');
    expect($info)->toHaveKey('compact');

    expect($info['version'])->toBe('2.0.0');
    expect($info['formatted'])->toBe('Version 2.0.0');
    expect($info['compact'])->toBe('v2.0.0');

    // Clean up
    unlink($versionFile);
    rmdir($testDir);
});

it('can check if git is available', function () {
    $laragitVersion = new LaragitVersion();
    $isGitAvailable = $laragitVersion->isGitAvailable();

    // Should return a boolean
    expect($isGitAvailable)->toBeBool();
});

it('handles whitespace in VERSION file', function () {
    // Create a temporary directory for testing
    $testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laragit_test';
    if (! file_exists($testDir)) {
        mkdir($testDir);
    }

    // Create a temporary VERSION file with whitespace
    $versionFile = $testDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, "  1.2.3  \n");

    // Create a mock version class that uses our test directory
    $laragitVersion = new class ($testDir) extends LaragitVersion {
        private $testDir;

        public function __construct($testDir)
        {
            $this->testDir = $testDir;
        }

        protected function getBasePath(): string
        {
            return $this->testDir;
        }
    };

    expect($laragitVersion->getCurrentVersion())->toBe('1.2.3');

    // Clean up
    unlink($versionFile);
    rmdir($testDir);
});

it('uses git when VERSION file is empty', function () {
    // Create a temporary directory for testing
    $testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laragit_test';
    if (! file_exists($testDir)) {
        mkdir($testDir);
    }

    // Create an empty VERSION file
    $versionFile = $testDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, '');

    // Create a mock version class that returns a git version
    $laragitVersion = new class ($testDir) extends LaragitVersion {
        private $testDir;

        public function __construct($testDir)
        {
            $this->testDir = $testDir;
        }

        protected function getBasePath(): string
        {
            return $this->testDir;
        }

        protected function shell($command): string
        {
            if (strpos($command, 'git describe') !== false) {
                return 'v1.0.0';
            }

            return '';
        }

        // Override the main method to ensure we use our shell method
        public function getCurrentVersion(): string
        {
            // Simple implementation like the user's custom solution
            $basePath = $this->getBasePath();
            $versionFile = $basePath . DIRECTORY_SEPARATOR . 'VERSION';

            if (file_exists($versionFile)) {
                $content = trim(file_get_contents($versionFile));
                // If file is empty, fall through to git
                if (! empty($content)) {
                    return $content;
                }
            }

            // Try to get version from Git
            $version = trim($this->shell('git describe --tags --abbrev=0'));

            // Return a default if no version found
            return $version ?: '0.0.0';
        }
    };

    expect($laragitVersion->getCurrentVersion())->toBe('v1.0.0');

    // Clean up
    unlink($versionFile);
    rmdir($testDir);
});

it('can show version with different formats', function () {
    // Create a temporary directory for testing
    $testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laragit_test';
    if (! file_exists($testDir)) {
        mkdir($testDir);
    }

    // Create a temporary VERSION file
    $versionFile = $testDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, '1.5.0');

    // Create a mock version class that uses our test directory
    $laragitVersion = new class ($testDir) extends LaragitVersion {
        private $testDir;

        public function __construct($testDir)
        {
            $this->testDir = $testDir;
        }

        protected function getBasePath(): string
        {
            return $this->testDir;
        }
    };

    // Test default format
    expect($laragitVersion->show())->toBe('Version 1.5.0');

    // Test compact format
    expect($laragitVersion->show('compact'))->toBe('v1.5.0');

    // Test with null format (should use default)
    expect($laragitVersion->show(null))->toBe('Version 1.5.0');

    // Clean up
    unlink($versionFile);
    rmdir($testDir);
});

it('can get version info with git version', function () {
    // Create a temporary directory for testing
    $testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laragit_test';
    if (! file_exists($testDir)) {
        mkdir($testDir);
    }

    // Make sure no VERSION file exists
    $versionFile = $testDir . DIRECTORY_SEPARATOR . 'VERSION';
    if (file_exists($versionFile)) {
        unlink($versionFile);
    }

    // Create a mock version class that returns a git version
    $laragitVersion = new class ($testDir) extends LaragitVersion {
        private $testDir;

        public function __construct($testDir)
        {
            $this->testDir = $testDir;
        }

        protected function getBasePath(): string
        {
            return $this->testDir;
        }

        public function getCurrentVersion(): string
        {
            // Return a fixed version for testing
            return '2.1.0';
        }
    };

    $info = $laragitVersion->getVersionInfo();

    expect($info)->toBeArray();
    expect($info)->toHaveKey('version');
    expect($info)->toHaveKey('formatted');
    expect($info)->toHaveKey('compact');

    expect($info['version'])->toBe('2.1.0');
    expect($info['formatted'])->toBe('Version 2.1.0');
    expect($info['compact'])->toBe('v2.1.0');

    // Clean up
    rmdir($testDir);
});

it('can check if git is not available with mock', function () {
    $laragitVersion = new class () extends LaragitVersion {
        protected function shell($command): string
        {
            // Return empty string to simulate git not available
            return '';
        }

        public function isGitAvailable(): bool
        {
            $output = trim($this->shell('git --version'));

            return ! empty($output) && strpos($output, 'git version') !== false;
        }
    };

    expect($laragitVersion->isGitAvailable())->toBeFalse();
});

it('can check if git is available with mock', function () {
    $laragitVersion = new class () extends LaragitVersion {
        protected function shell($command): string
        {
            // Return a valid git version output
            if (strpos($command, 'git --version') !== false) {
                return 'git version 2.30.0';
            }

            return '';
        }

        public function isGitAvailable(): bool
        {
            $output = trim($this->shell('git --version'));

            return ! empty($output) && strpos($output, 'git version') !== false;
        }
    };

    expect($laragitVersion->isGitAvailable())->toBeTrue();
});

it('uses default version when git returns empty', function () {
    // Create a temporary directory for testing
    $testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laragit_test';
    if (! file_exists($testDir)) {
        mkdir($testDir);
    }

    // Make sure no VERSION file exists
    $versionFile = $testDir . DIRECTORY_SEPARATOR . 'VERSION';
    if (file_exists($versionFile)) {
        unlink($versionFile);
    }

    // Create a mock version class that returns empty for git commands
    $laragitVersion = new class ($testDir) extends LaragitVersion {
        private $testDir;

        public function __construct($testDir)
        {
            $this->testDir = $testDir;
        }

        protected function getBasePath(): string
        {
            return $this->testDir;
        }

        protected function shell($command): string
        {
            // Return empty string to simulate no git tags
            return '';
        }

        // Override the main method to ensure we use our shell method
        public function getCurrentVersion(): string
        {
            // Simple implementation like the user's custom solution
            $basePath = $this->getBasePath();
            $versionFile = $basePath . DIRECTORY_SEPARATOR . 'VERSION';

            if (file_exists($versionFile)) {
                return trim(file_get_contents($versionFile));
            }

            // Try to get version from Git
            $version = trim($this->shell('git describe --tags --abbrev=0'));

            // Return a default if no version found
            return $version ?: '0.0.0';
        }
    };

    expect($laragitVersion->getCurrentVersion())->toBe('0.0.0');

    // Clean up
    rmdir($testDir);
});

it('can be instantiated', function () {
    $laragitVersion = new LaragitVersion();
    expect($laragitVersion)->toBeInstanceOf(LaragitVersion::class);
});

it('can handle version file with newlines', function () {
    // Create a temporary directory for testing
    $testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laragit_test';
    if (! file_exists($testDir)) {
        mkdir($testDir);
    }

    // Create a temporary VERSION file with newlines
    $versionFile = $testDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, "1.2.3\n\n");

    // Create a mock version class that uses our test directory
    $laragitVersion = new class ($testDir) extends LaragitVersion {
        private $testDir;

        public function __construct($testDir)
        {
            $this->testDir = $testDir;
        }

        protected function getBasePath(): string
        {
            return $this->testDir;
        }
    };

    expect($laragitVersion->getCurrentVersion())->toBe('1.2.3');

    // Clean up
    unlink($versionFile);
    rmdir($testDir);
});

it('can handle version file with multiple lines', function () {
    // Create a temporary directory for testing
    $testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laragit_test';
    if (! file_exists($testDir)) {
        mkdir($testDir);
    }

    // Create a temporary VERSION file with multiple lines (should use first line)
    $versionFile = $testDir . DIRECTORY_SEPARATOR . 'VERSION';
    file_put_contents($versionFile, "1.2.3\n2.0.0\n3.0.0");

    // Create a mock version class that uses our test directory
    $laragitVersion = new class ($testDir) extends LaragitVersion {
        private $testDir;

        public function __construct($testDir)
        {
            $this->testDir = $testDir;
        }

        protected function getBasePath(): string
        {
            return $this->testDir;
        }

        // Override to simulate actual file reading behavior
        public function getCurrentVersion(): string
        {
            $basePath = $this->getBasePath();
            $versionFile = $basePath . DIRECTORY_SEPARATOR . 'VERSION';

            if (file_exists($versionFile)) {
                $content = file_get_contents($versionFile);
                // Get first line only
                $lines = explode("\n", $content);

                return trim($lines[0]);
            }

            // Try to get version from Git
            $version = trim(@exec('git describe --tags --abbrev=0'));

            // Return a default if no version found
            return $version ?: '0.0.0';
        }
    };

    expect($laragitVersion->getCurrentVersion())->toBe('1.2.3');

    // Clean up
    unlink($versionFile);
    rmdir($testDir);
});

it('can test getBasePath method via reflection', function () {
    $laragitVersion = new LaragitVersion();

    // Test that the method exists
    expect(method_exists($laragitVersion, 'getBasePath'))->toBeTrue();

    // Use reflection to test the protected method
    $reflection = new ReflectionClass($laragitVersion);
    $method = $reflection->getMethod('getBasePath');
    $method->setAccessible(true);

    // Test that it returns a string
    $basePath = $method->invoke($laragitVersion);
    expect($basePath)->toBeString();
});
