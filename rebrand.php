<?php
/**
 * Morpheus Rebranding Script
 * Converts all DynamicCRUD references to Morpheus
 */

$replacements = [
    // Package names
    'dynamiccrud/dynamiccrud' => 'trymorpheus/morpheus',
    
    // Namespaces
    'namespace DynamicCRUD' => 'namespace Morpheus',
    'use DynamicCRUD\\' => 'use Morpheus\\',
    
    // Class names
    'class DynamicCRUD' => 'class Morpheus',
    'new DynamicCRUD(' => 'new Morpheus(',
    'DynamicCRUD::' => 'Morpheus::',
    
    // URLs
    'https://github.com/mcarbonell/DynamicCRUD' => 'https://github.com/trymorpheus/morpheus',
    'https://packagist.org/packages/dynamiccrud/dynamiccrud' => 'https://packagist.org/packages/trymorpheus/morpheus',
    
    // CLI commands
    'php bin/dynamiccrud' => 'php bin/morpheus',
    'bin/dynamiccrud' => 'bin/morpheus',
    
    // Project references
    'DynamicCRUD project' => 'Morpheus project',
    'DynamicCRUD is' => 'Morpheus is',
    'DynamicCRUD has' => 'Morpheus has',
    'DynamicCRUD v' => 'Morpheus v',
    'DynamicCRUD Test Suite' => 'Morpheus Test Suite',
    
    // Composer autoload
    '"DynamicCRUD\\\\": "src/"' => '"Morpheus\\\\": "src/"',
    '"DynamicCRUD\\\\Tests\\\\": "tests/"' => '"Morpheus\\\\Tests\\\\": "tests/"',
];

$directories = [
    __DIR__ . '/src',
    __DIR__ . '/tests',
    __DIR__ . '/examples',
    __DIR__ . '/docs',
    __DIR__ . '/install',
    __DIR__ . '/bin',
];

$files = [
    __DIR__ . '/README.md',
    __DIR__ . '/README.es.md',
    __DIR__ . '/composer.json',
    __DIR__ . '/phpunit.xml',
    __DIR__ . '/CHANGELOG.md',
    __DIR__ . '/CONTRIBUTING.md',
    __DIR__ . '/ROADMAP.md',
    __DIR__ . '/UNIVERSAL_CMS.md',
];

function processFile($file, $replacements) {
    if (!is_file($file)) return;
    
    $content = file_get_contents($file);
    $original = $content;
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "✓ Updated: $file\n";
    }
}

function processDirectory($dir, $replacements) {
    if (!is_dir($dir)) return;
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php', 'md', 'json', 'xml', 'html'])) {
            processFile($file->getPathname(), $replacements);
        }
    }
}

echo "🔄 Starting Morpheus Rebranding...\n\n";

// Process individual files
echo "📄 Processing root files...\n";
foreach ($files as $file) {
    processFile($file, $replacements);
}

// Process directories
echo "\n📁 Processing directories...\n";
foreach ($directories as $dir) {
    echo "  → $dir\n";
    processDirectory($dir, $replacements);
}

// Rename main class file
$oldFile = __DIR__ . '/src/DynamicCRUD.php';
$newFile = __DIR__ . '/src/Morpheus.php';
if (file_exists($oldFile) && !file_exists($newFile)) {
    rename($oldFile, $newFile);
    echo "\n✓ Renamed: DynamicCRUD.php → Morpheus.php\n";
}

// Rename CLI executable
$oldCli = __DIR__ . '/bin/dynamiccrud';
$newCli = __DIR__ . '/bin/morpheus';
if (file_exists($oldCli) && !file_exists($newCli)) {
    rename($oldCli, $newCli);
    echo "✓ Renamed: bin/dynamiccrud → bin/morpheus\n";
}

echo "\n✅ Rebranding complete!\n\n";
echo "Next steps:\n";
echo "1. Review changes: git diff\n";
echo "2. Update composer: composer update\n";
echo "3. Run tests: php vendor/phpunit/phpunit/phpunit\n";
echo "4. Commit: git add . && git commit -m 'Rebrand to Morpheus'\n";
echo "5. Push: git push origin main\n";
