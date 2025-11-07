<?php

require __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\Migration\MediaDownloader;

echo "📥 Testing Media Downloader\n\n";

// Create upload directory
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$downloader = new MediaDownloader($uploadDir);

echo "📁 Upload directory: {$uploadDir}\n\n";

// Test with placeholder images (these are real, publicly available test images)
$testUrls = [
    'https://via.placeholder.com/150/FF0000/FFFFFF?text=Test1',
    'https://via.placeholder.com/150/00FF00/FFFFFF?text=Test2',
    'https://via.placeholder.com/150/0000FF/FFFFFF?text=Test3'
];

echo "🔄 Downloading test images:\n";
echo "===========================\n";

foreach ($testUrls as $i => $url) {
    echo "  [" . ($i + 1) . "] Downloading: {$url}\n";
    $filename = $downloader->download($url);
    
    if ($filename) {
        echo "      ✅ Saved as: {$filename}\n";
        $filepath = $uploadDir . '/' . $filename;
        $size = filesize($filepath);
        echo "      Size: " . number_format($size) . " bytes\n";
    } else {
        echo "      ❌ Failed to download\n";
    }
    echo "\n";
}

echo "📊 Download Statistics:\n";
echo "======================\n";
echo "  Total downloaded: " . $downloader->getDownloadedCount() . "\n";
echo "  URL map:\n";
foreach ($downloader->getDownloadedMap() as $url => $filename) {
    echo "    {$url}\n";
    echo "    → {$filename}\n\n";
}

echo "🔄 Testing batch download:\n";
echo "==========================\n";

$batchUrls = [
    'https://via.placeholder.com/200/FFFF00/000000?text=Batch1',
    'https://via.placeholder.com/200/FF00FF/000000?text=Batch2'
];

$results = $downloader->downloadBatch($batchUrls);
echo "  Downloaded " . count($results) . " images\n";
foreach ($results as $url => $filename) {
    echo "    ✅ {$filename}\n";
}
echo "\n";

echo "🔄 Testing duplicate download (should use cache):\n";
echo "=================================================\n";

$duplicateUrl = $testUrls[0];
echo "  Downloading again: {$duplicateUrl}\n";
$filename = $downloader->download($duplicateUrl);
echo "  Result: {$filename} (from cache)\n\n";

echo "📁 Files in upload directory:\n";
echo "=============================\n";
$files = scandir($uploadDir);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $filepath = $uploadDir . '/' . $file;
        $size = filesize($filepath);
        echo "  - {$file} (" . number_format($size) . " bytes)\n";
    }
}
echo "\n";

echo "✅ Downloader test completed!\n";
echo "\n";
echo "💡 Note: Test images downloaded from placeholder.com\n";
echo "    You can view them in: {$uploadDir}/\n";
