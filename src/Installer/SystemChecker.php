<?php

namespace Morpheus\Installer;

class SystemChecker
{
    private array $requirements = [
        'php_version' => '8.0.0',
        'extensions' => ['pdo', 'json', 'fileinfo', 'mbstring'],
        'optional_extensions' => ['gd', 'curl', 'zip'],
    ];

    public function checkAll(): array
    {
        return [
            'php_version' => $this->checkPhpVersion(),
            'extensions' => $this->checkExtensions(),
            'optional_extensions' => $this->checkOptionalExtensions(),
            'writable_dirs' => $this->checkWritableDirectories(),
            'overall' => $this->isSystemReady(),
        ];
    }

    public function isSystemReady(): bool
    {
        $php = $this->checkPhpVersion();
        $ext = $this->checkExtensions();
        $dirs = $this->checkWritableDirectories();

        return $php['passed'] && $ext['passed'] && $dirs['passed'];
    }

    private function checkPhpVersion(): array
    {
        $current = PHP_VERSION;
        $required = $this->requirements['php_version'];
        $passed = version_compare($current, $required, '>=');

        return [
            'passed' => $passed,
            'current' => $current,
            'required' => $required,
            'message' => $passed 
                ? "PHP version $current is compatible" 
                : "PHP $required or higher required (current: $current)",
        ];
    }

    private function checkExtensions(): array
    {
        $missing = [];
        foreach ($this->requirements['extensions'] as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        return [
            'passed' => empty($missing),
            'required' => $this->requirements['extensions'],
            'missing' => $missing,
            'message' => empty($missing) 
                ? 'All required extensions are loaded' 
                : 'Missing extensions: ' . implode(', ', $missing),
        ];
    }

    private function checkOptionalExtensions(): array
    {
        $missing = [];
        foreach ($this->requirements['optional_extensions'] as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        return [
            'passed' => true, // Optional, always passes
            'recommended' => $this->requirements['optional_extensions'],
            'missing' => $missing,
            'message' => empty($missing) 
                ? 'All recommended extensions are loaded' 
                : 'Recommended extensions missing: ' . implode(', ', $missing),
        ];
    }

    private function checkWritableDirectories(): array
    {
        $dirs = ['cache', 'examples/uploads'];
        $notWritable = [];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if (!is_writable($dir)) {
                $notWritable[] = $dir;
            }
        }

        return [
            'passed' => empty($notWritable),
            'directories' => $dirs,
            'not_writable' => $notWritable,
            'message' => empty($notWritable) 
                ? 'All directories are writable' 
                : 'Not writable: ' . implode(', ', $notWritable),
        ];
    }
}
