<?php

use GenialDigitalNusantara\LaragitVersion\Helper\FileCommands;

describe('FileCommands', function () {
    describe('Basic File Operations', function () {
        it('can instantiate FileCommands class', function () {
            $fileCommands = new FileCommands();
            expect($fileCommands)->toBeInstanceOf(FileCommands::class);
        });

        it('can check if file exists', function () {
            $fileCommands = new FileCommands();
            $nonExistentFile = '/path/to/nonexistent/file.txt';
            expect($fileCommands->fileExists($nonExistentFile))->toBeFalse();
        });

        it('can get version file path', function () {
            $fileCommands = new FileCommands();
            $basePath = '/project/root';
            $fileName = 'VERSION';

            $result = $fileCommands->getVersionFilePath($basePath, $fileName);
            expect($result)->toContain('VERSION');
            expect($result)->toContain('project');
            expect($result)->toContain('root');
        });
    });

    describe('Version Content Parsing', function () {
        it('can parse version content with whitespace', function () {
            $fileCommands = new FileCommands();
            $content = "  1.0.0  \n";
            $result = $fileCommands->parseVersionContent($content);
            expect($result)->toBe('1.0.0');
        });

        it('can parse multiline version content', function () {
            $fileCommands = new FileCommands();
            $content = "\n\n1.2.3\nSome other content\n";
            $result = $fileCommands->parseVersionContent($content);
            expect($result)->toBe('1.2.3');
        });

        it('parses version content with complex formats', function () {
            $fileCommands = new FileCommands();
            
            $testCases = [
                ["v1.0.0\n", 'v1.0.0'],
                ["  version 2.1.0  \n\n", 'version 2.1.0'],
                ["ver 3.0.0-alpha.1\n", 'ver 3.0.0-alpha.1'],
                ["\nv4.2.1+build.123\n", 'v4.2.1+build.123'],
                ["1.5.0\nSome other text", '1.5.0'],
            ];
            
            foreach ($testCases as [$input, $expected]) {
                $result = $fileCommands->parseVersionContent($input);
                expect($result)->toBe($expected, "Input '$input' should parse to '$expected'");
            }
        });
        
        it('handles edge cases in version parsing', function () {
            $fileCommands = new FileCommands();
            
            // Test empty content
            $result = $fileCommands->parseVersionContent('');
            expect($result)->toBe('');
            
            // Test whitespace only
            $result = $fileCommands->parseVersionContent("  \n  \t  ");
            expect($result)->toBe('');
            
            // Test content without version - this actually returns the content as is
            $result = $fileCommands->parseVersionContent('no version here');
            expect($result)->toBe('no version here');
        });
    });

    describe('File Path Handling', function () {
        it('handles version file path with Windows directory separators', function () {
            $fileCommands = new FileCommands();
            $basePath = 'C:\\project\\root';
            $fileName = 'VERSION';

            $result = $fileCommands->getVersionFilePath($basePath, $fileName);
            expect($result)->toContain('VERSION');
            expect($result)->toContain('project');
        });

        it('handles cross-platform file paths correctly', function () {
            $fileCommands = new FileCommands();
            
            $unixPath = '/home/user/project';
            $windowsPath = 'C:\\Users\\User\\Project';
            
            $unixResult = $fileCommands->getVersionFilePath($unixPath, 'VERSION');
            expect($unixResult)->toContain('VERSION');
            expect($unixResult)->toContain('project');
            
            $windowsResult = $fileCommands->getVersionFilePath($windowsPath, 'VERSION');
            expect($windowsResult)->toContain('VERSION');
            expect($windowsResult)->toContain('Project');
        });
    });

    describe('File Validation', function () {
        it('handles version file content with basic format', function () {
            $fileCommands = new FileCommands();

            $validContents = [
                '1.0.0',
                'v2.1.3',
                '1.0.0-alpha.1',
                '2.0.0+build.123',
                'version 1.2.3',
            ];

            foreach ($validContents as $content) {
                // Create a temporary file for testing
                $tempFile = tempnam(sys_get_temp_dir(), 'version_test_');
                file_put_contents($tempFile, $content);

                $isValid = $fileCommands->isValidVersionFile($tempFile);
                expect($isValid)->toBeTrue("Content '$content' should be valid");

                unlink($tempFile);
            }
        });

        it('handles empty version files', function () {
            $fileCommands = new FileCommands();
            $tempFile = tempnam(sys_get_temp_dir(), 'empty_version_');
            file_put_contents($tempFile, '');
            
            expect($fileCommands->isValidVersionFile($tempFile))->toBeFalse();
            
            unlink($tempFile);
        });
        
        it('handles invalid version file content', function () {
            $fileCommands = new FileCommands();
            $tempFile = tempnam(sys_get_temp_dir(), 'invalid_version_');
            file_put_contents($tempFile, 'not a valid version');
            
            // This should actually be true since the regex allows alphanumeric and spaces
            expect($fileCommands->isValidVersionFile($tempFile))->toBeTrue();
            
            unlink($tempFile);
        });

        it('validates version file with different file states', function () {
            $fileCommands = new FileCommands();
            
            // Test with non-existent file
            expect($fileCommands->isValidVersionFile('/nonexistent/file'))->toBeFalse();
            
            // Test with a file path that would be a directory (but doesn't exist)
            expect($fileCommands->isValidVersionFile('/fake/directory/path'))->toBeFalse();
        });

        it('validates version file with length restrictions', function () {
            $fileCommands = new FileCommands();
            
            // Test with version content longer than 100 characters
            $longVersion = str_repeat('a', 101); // 101 characters
            $tempFile = tempnam(sys_get_temp_dir(), 'long_version_');
            file_put_contents($tempFile, $longVersion);
            
            expect($fileCommands->isValidVersionFile($tempFile))->toBeFalse();
            
            unlink($tempFile);
            
            // Test with version content exactly 100 characters (should be valid)
            $exactVersion = str_repeat('b', 100); // 100 characters
            $tempFile2 = tempnam(sys_get_temp_dir(), 'exact_version_');
            file_put_contents($tempFile2, $exactVersion);
            
            expect($fileCommands->isValidVersionFile($tempFile2))->toBeTrue();
            
            unlink($tempFile2);
        });
    });

    describe('File Reading Operations', function () {
        it('handles file read errors gracefully', function () {
            $fileCommands = new FileCommands();
            $nonExistentFile = '/path/to/nonexistent/file.txt';
            
            $result = $fileCommands->readFile($nonExistentFile);
            expect($result)->toBeFalse();
        });
        
        it('handles non-readable files', function () {
            $fileCommands = new FileCommands();
            $tempFile = tempnam(sys_get_temp_dir(), 'unreadable_test_');
            file_put_contents($tempFile, 'test content');
            
            // Test with file that exists but simulate read failure
            expect($fileCommands->isReadable($tempFile))->toBeTrue();
            
            // Test with non-existent file
            unlink($tempFile);
            expect($fileCommands->isReadable($tempFile))->toBeFalse();
        });

        it('handles readFile when file is not readable', function () {
            $fileCommands = new FileCommands();
            
            // Create a mock FileCommands that overrides isReadable to return false
            $mockFileCommands = new class extends FileCommands {
                public function isReadable($filePath): bool {
                    return false; // Simulate unreadable file
                }
            };
            
            $tempFile = tempnam(sys_get_temp_dir(), 'readable_test_');
            file_put_contents($tempFile, 'test content');
            
            // This should return false due to the mocked isReadable
            $result = $mockFileCommands->readFile($tempFile);
            expect($result)->toBeFalse();
            
            unlink($tempFile);
        });

        it('gets version from file with error handling', function () {
            $fileCommands = new FileCommands();
            $nonExistentFile = '/path/to/nonexistent/version.txt';
            
            $result = $fileCommands->getVersionFromFile($nonExistentFile);
            expect($result)->toBe('');
        });
        
        it('gets version from valid file', function () {
            $fileCommands = new FileCommands();
            $tempFile = tempnam(sys_get_temp_dir(), 'valid_version_');
            file_put_contents($tempFile, 'v1.2.3');
            
            $result = $fileCommands->getVersionFromFile($tempFile);
            expect($result)->toBe('v1.2.3');
            
            unlink($tempFile);
        });
    });
});