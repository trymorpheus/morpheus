<?php

require __DIR__ . '/../../vendor/autoload.php';

use Morpheus\ContentTypes\ContentTypeManager;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Setting up Theme Showcase...\n\n";

// Install blog content type with prefix
$manager = new ContentTypeManager($pdo);

if ($manager->isInstalled('blog')) {
    echo "✓ Blog already installed\n";
} else {
    echo "Installing blog content type...\n";
    $manager->install('blog');
    echo "✓ Blog installed\n";
}

echo "\n✅ Setup complete!\n";
echo "\nNext steps:\n";
echo "1. Visit index.php to see the theme showcase\n";
echo "2. Use the theme switcher to change themes\n";
echo "3. See how each theme renders the same content differently\n";
