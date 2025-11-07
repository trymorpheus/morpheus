<?php

require __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\Migration\WXRParser;
use DynamicCRUD\Migration\ContentMapper;

echo "🔄 Testing Content Mapper\n\n";

// Parse WordPress export
$parser = new WXRParser();
$data = $parser->parse(__DIR__ . '/sample.xml');

// Create mapper
$mapper = new ContentMapper();

echo "📦 Mapping Categories:\n";
echo "=====================\n";
$categoryMap = [];
foreach ($data['categories'] as $wpCat) {
    $mapped = $mapper->mapCategory($wpCat);
    echo "  WP: {$wpCat['name']} (slug: {$wpCat['slug']})\n";
    echo "  →  DC: name={$mapped['name']}, slug={$mapped['slug']}\n\n";
    
    // Simulate database ID
    $categoryMap[$wpCat['slug']] = count($categoryMap) + 1;
}

echo "🏷️  Mapping Tags:\n";
echo "================\n";
$tagMap = [];
foreach ($data['tags'] as $wpTag) {
    $mapped = $mapper->mapTag($wpTag);
    echo "  WP: {$wpTag['name']} (slug: {$wpTag['slug']})\n";
    echo "  →  DC: name={$mapped['name']}, slug={$mapped['slug']}\n\n";
    
    // Simulate database ID
    $tagMap[$wpTag['slug']] = count($tagMap) + 1;
}

// Set maps for post mapping
$mapper->setCategoryMap($categoryMap);
$mapper->setTagMap($tagMap);

echo "📝 Mapping Posts:\n";
echo "=================\n";
foreach ($data['posts'] as $wpPost) {
    $mapped = $mapper->mapPost($wpPost);
    $tags = $mapper->mapPostTags($wpPost);
    
    echo "  WP Post: {$wpPost['title']}\n";
    echo "    Status: {$wpPost['status']} → {$mapped['status']}\n";
    echo "    Slug: {$mapped['slug']}\n";
    echo "    Category ID: {$mapped['category_id']}\n";
    echo "    Tags: [" . implode(', ', $tags) . "]\n";
    echo "    Content length: " . strlen($mapped['content']) . " chars\n";
    echo "    Published: " . ($mapped['published_at'] ?? 'null') . "\n";
    echo "    WP ID: {$mapped['wp_id']}\n";
    echo "    WP Link: {$mapped['wp_link']}\n";
    
    // Test image extraction
    $images = $mapper->extractImageUrls($mapped['content']);
    if (!empty($images)) {
        echo "    Images found: " . count($images) . "\n";
        foreach ($images as $img) {
            echo "      - {$img}\n";
        }
    }
    
    echo "\n";
}

echo "🔄 Testing Content Conversion:\n";
echo "==============================\n";

$testContent = <<<HTML
<p>Test paragraph</p>
[caption]Image caption[/caption]
[gallery ids="1,2,3"]
<img class="aligncenter" src="test.jpg" />
<img class="alignleft" src="left.jpg" />
<img class="alignright" src="right.jpg" />
HTML;

$mapper2 = new ContentMapper();
$converted = $mapper2->mapPost([
    'title' => 'Test',
    'slug' => 'test',
    'content' => $testContent,
    'excerpt' => '',
    'status' => 'publish',
    'published_at' => '2024-01-15 10:00:00',
    'categories' => [],
    'tags' => [],
    'id' => 999,
    'link' => 'https://example.com/test'
]);

echo "Original content:\n";
echo $testContent . "\n\n";
echo "Converted content:\n";
echo $converted['content'] . "\n\n";

echo "🔄 Testing URL Replacement:\n";
echo "===========================\n";

$contentWithImages = '<img src="https://old.com/image1.jpg" /><img src="https://old.com/image2.jpg" />';
$urlMap = [
    'https://old.com/image1.jpg' => '/uploads/image1.jpg',
    'https://old.com/image2.jpg' => '/uploads/image2.jpg'
];

$replaced = $mapper2->replaceImageUrls($contentWithImages, $urlMap);
echo "Original: {$contentWithImages}\n";
echo "Replaced: {$replaced}\n\n";

echo "✅ Mapper test completed!\n";
