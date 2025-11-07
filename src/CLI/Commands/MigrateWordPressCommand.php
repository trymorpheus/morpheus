<?php

namespace DynamicCRUD\CLI\Commands;

use DynamicCRUD\Migration\WordPressMigrator;

class MigrateWordPressCommand
{
    private array $options = [];
    
    public function execute(array $args): int
    {
        $this->parseArguments($args);
        
        if (isset($this->options['help'])) {
            $this->showHelp();
            return 0;
        }
        
        if (!isset($args[0])) {
            echo "❌ Error: WXR file path required\n\n";
            $this->showHelp();
            return 1;
        }
        
        $wxrFile = $args[0];
        
        if (!file_exists($wxrFile)) {
            echo "❌ Error: File not found: {$wxrFile}\n";
            return 1;
        }
        
        return $this->runMigration($wxrFile);
    }
    
    private function runMigration(string $wxrFile): int
    {
        $pdo = $this->createPDO();
        $prefix = $this->options['prefix'] ?? '';
        $uploadDir = $this->options['upload-dir'] ?? getcwd() . '/uploads';
        $downloadMedia = !isset($this->options['no-media']);
        $dryRun = isset($this->options['dry-run']);
        
        echo "🔄 WordPress to DynamicCRUD Migration\n";
        echo str_repeat('=', 50) . "\n\n";
        
        echo "📋 Configuration:\n";
        echo "  WXR File: {$wxrFile}\n";
        echo "  Table Prefix: " . ($prefix ?: '(none)') . "\n";
        echo "  Upload Dir: {$uploadDir}\n";
        echo "  Download Media: " . ($downloadMedia ? 'Yes' : 'No') . "\n";
        echo "  Dry Run: " . ($dryRun ? 'Yes' : 'No') . "\n\n";
        
        if ($dryRun) {
            echo "ℹ️  DRY RUN MODE - No changes will be made\n\n";
        }
        
        $migrator = new WordPressMigrator($pdo, $prefix, $downloadMedia ? $uploadDir : null);
        
        echo "🚀 Starting migration...\n";
        $this->showProgress('Parsing WXR file');
        
        $startTime = microtime(true);
        $result = $migrator->migrate($wxrFile, [
            'download_media' => $downloadMedia
        ]);
        $duration = round(microtime(true) - $startTime, 2);
        
        if (!$result['success']) {
            echo "\n❌ Migration failed!\n";
            echo "Error: {$result['error']}\n";
            return 1;
        }
        
        echo "\n\n✅ Migration completed successfully!\n\n";
        
        $this->showStatistics($result['stats'], $duration);
        
        if (!empty($result['stats']['errors'])) {
            $this->showErrors($result['stats']['errors']);
        }
        
        if (!empty($result['url_map'])) {
            $this->showUrlMappings($result['url_map']);
        }
        
        if (isset($this->options['generate-redirects'])) {
            $this->generateRedirects($migrator);
        }
        
        return 0;
    }
    
    private function showProgress(string $message): void
    {
        echo "  ⏳ {$message}...";
        flush();
    }
    
    private function showStatistics(array $stats, float $duration): void
    {
        echo "📊 Statistics:\n";
        echo "  Categories imported: {$stats['categories']}\n";
        echo "  Tags imported: {$stats['tags']}\n";
        echo "  Posts imported: {$stats['posts']}\n";
        echo "  Media downloaded: {$stats['media']}\n";
        echo "  Errors: " . count($stats['errors']) . "\n";
        echo "  Duration: {$duration}s\n\n";
    }
    
    private function showErrors(array $errors): void
    {
        echo "⚠️  Errors encountered:\n";
        foreach ($errors as $error) {
            echo "  - {$error['post']}: {$error['error']}\n";
        }
        echo "\n";
    }
    
    private function showUrlMappings(array $urlMap): void
    {
        if (isset($this->options['verbose'])) {
            echo "🔗 URL Mappings:\n";
            foreach ($urlMap as $oldUrl => $newUrl) {
                echo "  {$oldUrl}\n";
                echo "  → {$newUrl}\n\n";
            }
        } else {
            echo "🔗 URL Mappings: " . count($urlMap) . " URLs mapped\n";
            echo "   (use --verbose to see all mappings)\n\n";
        }
    }
    
    private function generateRedirects(WordPressMigrator $migrator): void
    {
        $format = $this->options['redirect-format'] ?? 'htaccess';
        $output = $this->options['redirect-output'] ?? 'redirects.' . $format;
        
        echo "📄 Generating redirects ({$format})...\n";
        $content = $migrator->generateRedirects($format);
        file_put_contents($output, $content);
        echo "  ✅ Saved to: {$output}\n\n";
    }
    
    private function createPDO(): \PDO
    {
        $host = $this->options['host'] ?? 'localhost';
        $database = $this->options['database'] ?? 'test';
        $username = $this->options['username'] ?? 'root';
        $password = $this->options['password'] ?? 'rootpassword';
        
        $pdo = new \PDO("mysql:host={$host};dbname={$database}", $username, $password);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    }
    
    private function parseArguments(array $args): void
    {
        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                $parts = explode('=', substr($arg, 2), 2);
                $key = $parts[0];
                $value = $parts[1] ?? true;
                $this->options[$key] = $value;
            }
        }
    }
    
    private function showHelp(): void
    {
        echo <<<HELP
WordPress Migration Tool

Usage:
  php bin/dynamiccrud migrate:wordpress <wxr-file> [options]

Arguments:
  wxr-file              Path to WordPress export file (WXR format)

Options:
  --prefix=PREFIX       Table prefix (default: none)
  --host=HOST           Database host (default: localhost)
  --database=DB         Database name (default: test)
  --username=USER       Database username (default: root)
  --password=PASS       Database password (default: rootpassword)
  --upload-dir=DIR      Upload directory (default: ./uploads)
  --no-media            Skip media download
  --dry-run             Preview migration without making changes
  --generate-redirects  Generate redirect rules
  --redirect-format=FMT Redirect format: htaccess|nginx (default: htaccess)
  --redirect-output=OUT Output file for redirects (default: redirects.FORMAT)
  --verbose             Show detailed output
  --help                Show this help message

Examples:
  # Basic migration
  php bin/dynamiccrud migrate:wordpress export.xml

  # With table prefix
  php bin/dynamiccrud migrate:wordpress export.xml --prefix=wp_

  # Skip media download
  php bin/dynamiccrud migrate:wordpress export.xml --no-media

  # Generate redirects
  php bin/dynamiccrud migrate:wordpress export.xml --generate-redirects

  # Dry run (preview only)
  php bin/dynamiccrud migrate:wordpress export.xml --dry-run

  # Custom database
  php bin/dynamiccrud migrate:wordpress export.xml \\
    --host=localhost \\
    --database=mydb \\
    --username=user \\
    --password=pass

HELP;
    }
}
