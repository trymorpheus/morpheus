<?php

require __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\Migration\WXRParser;

echo "🔍 Testing WordPress XML Parser\n\n";

$parser = new WXRParser();
$data = $parser->parse(__DIR__ . '/sample.xml');

echo "📊 Parsing Results:\n";
echo "==================\n\n";

echo "Site Information:\n";
echo "  Title: {$data['site']['title']}\n";
echo "  Link: {$data['site']['link']}\n";
echo "  Language: {$data['site']['language']}\n\n";

echo "Categories: " . count($data['categories']) . "\n";
foreach ($data['categories'] as $cat) {
    echo "  - {$cat['name']} (slug: {$cat['slug']})\n";
}
echo "\n";

echo "Tags: " . count($data['tags']) . "\n";
foreach ($data['tags'] as $tag) {
    echo "  - {$tag['name']} (slug: {$tag['slug']})\n";
}
echo "\n";

echo "Authors: " . count($data['authors']) . "\n";
foreach ($data['authors'] as $author) {
    echo "  - {$author['display_name']} ({$author['email']})\n";
}
echo "\n";

echo "Posts: " . count($data['posts']) . "\n";
foreach ($data['posts'] as $post) {
    echo "  - {$post['title']}\n";
    echo "    Status: {$post['status']}\n";
    echo "    Slug: {$post['slug']}\n";
    echo "    Categories: " . implode(', ', $post['categories']) . "\n";
    echo "    Tags: " . implode(', ', $post['tags']) . "\n";
    echo "    Content length: " . strlen($post['content']) . " chars\n";
    echo "\n";
}

echo "✅ Parser test completed!\n";
