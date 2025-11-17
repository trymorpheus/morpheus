<?php

require __DIR__ . '/../../vendor/autoload.php';

use Morpheus\Theme\ThemeManager;
use Morpheus\Theme\Themes\MinimalTheme;
use Morpheus\Theme\Themes\ModernTheme;
use Morpheus\Theme\Themes\ClassicTheme;
use Morpheus\Frontend\FrontendRenderer;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize ThemeManager
$themesDir = __DIR__ . '/../../themes';
$themeManager = new ThemeManager($pdo, $themesDir);

// Register themes
$themeManager->register('minimal', new MinimalTheme($themesDir));
$themeManager->register('modern', new ModernTheme($themesDir));
$themeManager->register('classic', new ClassicTheme($themesDir));

// Get active theme or use minimal as default
$activeTheme = $themeManager->getActive();
if (!$activeTheme) {
    $themeManager->activate('minimal');
}

// Create renderer with theme (using 24_ prefix from example 24)
$renderer = new FrontendRenderer($pdo, 'blog', null, null, '24_', $themeManager);

// Render home page
echo $renderer->renderHome();
