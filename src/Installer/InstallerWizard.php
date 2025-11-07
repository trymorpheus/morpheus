<?php

namespace DynamicCRUD\Installer;

use DynamicCRUD\ContentTypes\ContentTypeManager;
use DynamicCRUD\Metadata\GlobalMetadata;
use PDO;

class InstallerWizard
{
    private SystemChecker $systemChecker;
    private DatabaseSetup $databaseSetup;
    private ConfigGenerator $configGenerator;
    private array $steps = [
        'welcome',
        'system_check',
        'database',
        'site_info',
        'content_type',
        'theme',
        'install',
        'success',
    ];

    public function __construct()
    {
        $this->systemChecker = new SystemChecker();
        $this->databaseSetup = new DatabaseSetup();
        $this->configGenerator = new ConfigGenerator();
    }

    public function getCurrentStep(): string
    {
        return $_GET['step'] ?? 'welcome';
    }

    public function getNextStep(string $current): ?string
    {
        $index = array_search($current, $this->steps);
        return $this->steps[$index + 1] ?? null;
    }

    public function getPreviousStep(string $current): ?string
    {
        $index = array_search($current, $this->steps);
        return $index > 0 ? $this->steps[$index - 1] : null;
    }

    public function checkSystem(): array
    {
        return $this->systemChecker->checkAll();
    }

    public function testDatabaseConnection(array $config): array
    {
        return $this->databaseSetup->testConnection(
            $config['host'],
            $config['database'],
            $config['username'],
            $config['password'],
            $config['driver'] ?? 'mysql'
        );
    }

    public function install(array $config): array
    {
        try {
            $steps = [];

            // Step 1: Test database connection
            $steps['connection'] = $this->databaseSetup->testConnection(
                $config['host'],
                $config['database'],
                $config['username'],
                $config['password'],
                $config['driver'] ?? 'mysql'
            );

            if (!$steps['connection']['success']) {
                return ['success' => false, 'error' => 'Database connection failed', 'steps' => $steps];
            }

            // Step 2: Create core tables
            $steps['tables'] = $this->databaseSetup->createCoreTables();
            if (!$steps['tables']['success']) {
                return ['success' => false, 'error' => 'Failed to create tables', 'steps' => $steps];
            }

            // Step 3: Create admin user
            $steps['admin'] = $this->databaseSetup->createAdminUser(
                $config['admin_name'],
                $config['admin_email'],
                $config['admin_password']
            );

            if (!$steps['admin']['success']) {
                return ['success' => false, 'error' => 'Failed to create admin user', 'steps' => $steps];
            }

            // Step 4: Install content type (if selected)
            if (!empty($config['content_type'])) {
                $pdo = $this->databaseSetup->getPDO();
                $manager = new ContentTypeManager($pdo);
                $steps['content_type'] = $manager->install($config['content_type']);
            }

            // Step 5: Set theme (if selected)
            if (!empty($config['theme'])) {
                $pdo = $this->databaseSetup->getPDO();
                $globalConfig = new GlobalMetadata($pdo);
                $globalConfig->set('theme.active', $config['theme']);
                $steps['theme'] = ['success' => true, 'theme' => $config['theme']];
            }

            // Step 6: Generate config file
            $configContent = $this->configGenerator->generate([
                'driver' => $config['driver'] ?? 'mysql',
                'host' => $config['host'],
                'database' => $config['database'],
                'username' => $config['username'],
                'password' => $config['password'],
                'site_title' => $config['site_title'],
                'site_url' => $config['site_url'] ?? '',
                'language' => $config['language'] ?? 'en',
            ]);

            $steps['config'] = $this->configGenerator->save($configContent);

            return [
                'success' => true,
                'message' => 'Installation completed successfully',
                'steps' => $steps,
                'admin_url' => '/admin.php',
                'site_url' => '/',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'steps' => $steps ?? [],
            ];
        }
    }

    public function getAvailableContentTypes(): array
    {
        return [
            'blog' => [
                'name' => 'Blog',
                'description' => 'WordPress-style blog with posts, categories, and tags',
                'icon' => '📝',
                'tables' => 5,
            ],
            'none' => [
                'name' => 'Empty Site',
                'description' => 'Start with a clean slate',
                'icon' => '🎯',
                'tables' => 0,
            ],
        ];
    }

    public function getAvailableThemes(): array
    {
        return [
            'minimal' => [
                'name' => 'Minimal',
                'description' => 'Clean and simple design',
                'preview' => '/themes/minimal/preview.png',
            ],
            'modern' => [
                'name' => 'Modern',
                'description' => 'Contemporary and professional',
                'preview' => '/themes/modern/preview.png',
            ],
            'classic' => [
                'name' => 'Classic',
                'description' => 'Traditional and elegant',
                'preview' => '/themes/classic/preview.png',
            ],
        ];
    }
}
