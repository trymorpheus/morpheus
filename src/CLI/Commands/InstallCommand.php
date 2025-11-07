<?php

namespace DynamicCRUD\CLI\Commands;

use DynamicCRUD\Installer\InstallerWizard;

class InstallCommand
{
    private InstallerWizard $wizard;

    public function __construct()
    {
        $this->wizard = new InstallerWizard();
    }

    public function execute(array $args): int
    {
        echo "🚀 DynamicCRUD Installer\n";
        echo str_repeat('=', 50) . "\n\n";

        // Check if interactive mode
        $interactive = in_array('--interactive', $args) || empty($args);

        if ($interactive) {
            return $this->runInteractive();
        }

        return $this->runNonInteractive($args);
    }

    private function runInteractive(): int
    {
        // Step 1: System check
        echo "📋 Checking system requirements...\n";
        $systemCheck = $this->wizard->checkSystem();

        if (!$systemCheck['overall']) {
            echo "❌ System check failed:\n";
            echo "   PHP: " . $systemCheck['php_version']['message'] . "\n";
            echo "   Extensions: " . $systemCheck['extensions']['message'] . "\n";
            echo "   Directories: " . $systemCheck['writable_dirs']['message'] . "\n";
            return 1;
        }

        echo "✅ System check passed\n\n";

        // Step 2: Database configuration
        echo "🗄️  Database Configuration\n";
        $dbConfig = $this->promptDatabaseConfig();

        // Test connection
        echo "Testing database connection...\n";
        $connTest = $this->wizard->testDatabaseConnection($dbConfig);

        if (!$connTest['success']) {
            echo "❌ " . $connTest['message'] . "\n";
            return 1;
        }

        echo "✅ Database connection successful\n\n";

        // Step 3: Site information
        echo "🌐 Site Information\n";
        $siteConfig = $this->promptSiteConfig();

        // Step 4: Admin user
        echo "👤 Admin User\n";
        $adminConfig = $this->promptAdminConfig();

        // Step 5: Content type
        echo "📦 Content Type\n";
        $contentType = $this->promptContentType();

        // Step 6: Theme
        echo "🎨 Theme\n";
        $theme = $this->promptTheme();

        // Merge all config
        $config = array_merge($dbConfig, $siteConfig, $adminConfig, [
            'content_type' => $contentType,
            'theme' => $theme,
        ]);

        // Step 7: Install
        echo "\n🔧 Installing...\n";
        $result = $this->wizard->install($config);

        if (!$result['success']) {
            echo "❌ Installation failed: " . ($result['error'] ?? 'Unknown error') . "\n";
            return 1;
        }

        // Success
        echo "\n✅ Installation completed successfully!\n\n";
        echo "📊 Summary:\n";
        echo "   Site Title: " . $config['site_title'] . "\n";
        echo "   Admin Email: " . $config['admin_email'] . "\n";
        echo "   Content Type: " . ($contentType ?: 'None') . "\n";
        echo "   Theme: " . ($theme ?: 'Default') . "\n\n";
        echo "🎉 Your site is ready!\n";
        echo "   Admin Panel: http://localhost/admin.php\n";
        echo "   Frontend: http://localhost/\n\n";

        return 0;
    }

    private function runNonInteractive(array $args): int
    {
        $config = $this->parseArgs($args);

        // Validate required fields
        $required = ['host', 'database', 'username', 'admin-email', 'admin-password'];
        foreach ($required as $field) {
            if (empty($config[str_replace('-', '_', $field)])) {
                echo "❌ Missing required argument: --$field\n";
                return 1;
            }
        }

        echo "🔧 Installing with provided configuration...\n";
        $result = $this->wizard->install($config);

        if (!$result['success']) {
            echo "❌ Installation failed: " . ($result['error'] ?? 'Unknown error') . "\n";
            return 1;
        }

        echo "✅ Installation completed successfully!\n";
        return 0;
    }

    private function promptDatabaseConfig(): array
    {
        return [
            'driver' => $this->prompt('Database driver (mysql/pgsql)', 'mysql'),
            'host' => $this->prompt('Database host', 'localhost'),
            'database' => $this->prompt('Database name', 'dynamiccrud'),
            'username' => $this->prompt('Database username', 'root'),
            'password' => $this->prompt('Database password', '', true),
        ];
    }

    private function promptSiteConfig(): array
    {
        return [
            'site_title' => $this->prompt('Site title', 'My Site'),
            'site_url' => $this->prompt('Site URL', 'http://localhost'),
            'language' => $this->prompt('Language (en/es/fr)', 'en'),
        ];
    }

    private function promptAdminConfig(): array
    {
        return [
            'admin_name' => $this->prompt('Admin name', 'Admin'),
            'admin_email' => $this->prompt('Admin email', 'admin@example.com'),
            'admin_password' => $this->prompt('Admin password', '', true),
        ];
    }

    private function promptContentType(): string
    {
        $types = $this->wizard->getAvailableContentTypes();
        echo "Available content types:\n";
        foreach ($types as $key => $type) {
            echo "  - $key: {$type['name']} - {$type['description']}\n";
        }
        return $this->prompt('Content type', 'blog');
    }

    private function promptTheme(): string
    {
        $themes = $this->wizard->getAvailableThemes();
        echo "Available themes:\n";
        foreach ($themes as $key => $theme) {
            echo "  - $key: {$theme['name']} - {$theme['description']}\n";
        }
        return $this->prompt('Theme', 'minimal');
    }

    private function prompt(string $question, string $default = '', bool $hidden = false): string
    {
        $defaultText = $default ? " [$default]" : '';
        echo "$question$defaultText: ";

        if ($hidden && function_exists('readline')) {
            $input = readline();
        } else {
            $input = trim(fgets(STDIN));
        }

        return $input ?: $default;
    }

    private function parseArgs(array $args): array
    {
        $config = [];
        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                $parts = explode('=', substr($arg, 2), 2);
                if (count($parts) === 2) {
                    $config[str_replace('-', '_', $parts[0])] = $parts[1];
                }
            }
        }
        return $config;
    }
}
