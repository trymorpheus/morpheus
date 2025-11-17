<?php

require __DIR__ . '/../../vendor/autoload.php';

use Morpheus\Migration\WordPressMigrator;

echo "🔄 WordPress to DynamicCRUD Migration\n";
echo "=====================================\n\n";

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Configuration
$wxrFile = __DIR__ . '/sample.xml';
$prefix = 'wp_';
$uploadDir = __DIR__ . '/uploads';

echo "📋 Configuration:\n";
echo "  WXR File: {$wxrFile}\n";
echo "  Table Prefix: {$prefix}\n";
echo "  Upload Dir: {$uploadDir}\n\n";

// Ensure tables exist (simplified - assumes tables are already created)
echo "🔍 Checking database tables...\n";
try {
    $pdo->query("SELECT 1 FROM {$prefix}posts LIMIT 1");
    $pdo->query("SELECT 1 FROM {$prefix}categories LIMIT 1");
    $pdo->query("SELECT 1 FROM {$prefix}tags LIMIT 1");
    $pdo->query("SELECT 1 FROM {$prefix}post_tags LIMIT 1");
    echo "  ✅ All required tables exist\n\n";
} catch (\PDOException $e) {
    echo "  ❌ Required tables not found\n";
    echo "  💡 Please run the blog CMS setup first:\n";
    echo "     php examples/24-blog-cms/setup.php\n";
    echo "     (or create tables with prefix '{$prefix}')\n\n";
    exit(1);
}

// Create migrator
echo "🚀 Starting migration...\n\n";
$migrator = new WordPressMigrator($pdo, $prefix, $uploadDir);

// Run migration
$startTime = microtime(true);
$result = $migrator->migrate($wxrFile, [
    'download_media' => false // Disable for this demo
]);
$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

// Display results
if ($result['success']) {
    echo "✅ Migration completed successfully!\n\n";
    
    echo "📊 Statistics:\n";
    echo "  Categories imported: {$result['stats']['categories']}\n";
    echo "  Tags imported: {$result['stats']['tags']}\n";
    echo "  Posts imported: {$result['stats']['posts']}\n";
    echo "  Media downloaded: {$result['stats']['media']}\n";
    echo "  Errors: " . count($result['stats']['errors']) . "\n";
    echo "  Duration: {$duration}s\n\n";
    
    if (!empty($result['stats']['errors'])) {
        echo "⚠️  Errors encountered:\n";
        foreach ($result['stats']['errors'] as $error) {
            echo "  - {$error['post']}: {$error['error']}\n";
        }
        echo "\n";
    }
    
    echo "🔗 URL Mappings:\n";
    foreach ($result['url_map'] as $oldUrl => $newUrl) {
        echo "  {$oldUrl}\n";
        echo "  → {$newUrl}\n\n";
    }
    
    // Generate redirects
    echo "📄 Generating redirects...\n";
    $htaccess = $migrator->generateRedirects('htaccess');
    file_put_contents(__DIR__ . '/redirects.htaccess', $htaccess);
    echo "  ✅ Saved to: redirects.htaccess\n\n";
    
    echo "🎉 Migration complete! You can now:\n";
    echo "  1. View your blog at: http://localhost:8000/examples/24-blog-cms/\n";
    echo "  2. Check the admin panel: http://localhost:8000/examples/24-blog-cms/admin.php\n";
    echo "  3. Copy redirects.htaccess to your web root\n";
    
} else {
    echo "❌ Migration failed!\n\n";
    echo "Error: {$result['error']}\n\n";
    
    echo "📊 Partial Statistics:\n";
    echo "  Categories imported: {$result['stats']['categories']}\n";
    echo "  Tags imported: {$result['stats']['tags']}\n";
    echo "  Posts imported: {$result['stats']['posts']}\n";
    echo "  Media downloaded: {$result['stats']['media']}\n";
}
