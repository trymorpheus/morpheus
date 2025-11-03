<?php
/**
 * Clear all cache files
 */

$cacheDir = __DIR__ . '/../cache';
$templatesCache = $cacheDir . '/templates';

function deleteFiles($dir) {
    if (!is_dir($dir)) {
        return;
    }
    
    $files = glob($dir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

// Clear schema cache
deleteFiles($cacheDir);

// Clear template cache
deleteFiles($templatesCache);

echo "✅ Cache cleared successfully!\n";
echo "- Schema cache: " . $cacheDir . "\n";
echo "- Template cache: " . $templatesCache . "\n";
?>
