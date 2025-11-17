<?php

require __DIR__ . '/../../vendor/autoload.php';

use Morpheus\Theme\ThemeManager;
use Morpheus\Theme\Themes\MinimalTheme;
use Morpheus\Theme\Themes\ModernTheme;
use Morpheus\Theme\Themes\ClassicTheme;
use Morpheus\Frontend\FrontendRenderer;
use Morpheus\Frontend\FrontendRouter;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize ThemeManager
$themesDir = __DIR__ . '/../../themes';
$themeManager = new ThemeManager($pdo, $themesDir);

// Register themes
$themeManager->register('minimal', new MinimalTheme($themesDir));
$themeManager->register('modern', new ModernTheme($themesDir));
$themeManager->register('classic', new ClassicTheme($themesDir));

// Handle theme switching
if (isset($_GET['theme']) && in_array($_GET['theme'], ['minimal', 'modern', 'classic'])) {
    $themeManager->activate($_GET['theme']);
    header('Location: index.php');
    exit;
}

// Activate default theme if none active
if (!$themeManager->getActive()) {
    $themeManager->activate('minimal');
}

// Reload active theme after potential activation
$activeTheme = $themeManager->getActive();
$availableThemes = $themeManager->getAvailable();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Showcase - DynamicCRUD</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, sans-serif; background: #f5f7fa; }
        .header { background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 30px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        h1 { color: #667eea; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 20px; }
        .theme-switcher { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .theme-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .theme-card { background: white; border: 2px solid #e0e0e0; border-radius: 8px; padding: 20px; transition: all 0.3s; cursor: pointer; }
        .theme-card:hover { border-color: #667eea; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .theme-card.active { border-color: #667eea; background: #f0f4ff; }
        .theme-card h3 { color: #333; margin-bottom: 10px; }
        .theme-card p { color: #666; font-size: 14px; margin-bottom: 15px; }
        .theme-card .badge { display: inline-block; background: #667eea; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .theme-card .badge.active { background: #4caf50; }
        .features { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .features h2 { margin-bottom: 20px; color: #333; }
        .feature-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
        .feature-item { padding: 15px; background: #f5f7fa; border-radius: 6px; }
        .feature-item strong { color: #667eea; display: block; margin-bottom: 5px; }
        .demo-section { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .demo-section h2 { margin-bottom: 20px; color: #333; }
        .btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; transition: background 0.3s; }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🎨 Theme Showcase</h1>
            <p class="subtitle">Experience DynamicCRUD with different themes</p>
        </div>
    </div>

    <div class="container">
        <div class="theme-switcher">
            <h2>Available Themes</h2>
            <p style="color: #666; margin-bottom: 20px;">Click on a theme to activate it</p>
            
            <div class="theme-grid">
                <?php foreach ($availableThemes as $name => $info): ?>
                    <a href="?theme=<?= $name ?>" style="text-decoration: none;">
                        <div class="theme-card <?= $activeTheme && $activeTheme->getName() === $info['name'] ? 'active' : '' ?>">
                            <h3><?= htmlspecialchars($info['name']) ?></h3>
                            <p><?= htmlspecialchars($info['description']) ?></p>
                            <span class="badge <?= $activeTheme && $activeTheme->getName() === $info['name'] ? 'active' : '' ?>">
                                <?= $activeTheme && $activeTheme->getName() === $info['name'] ? '✓ Active' : 'Activate' ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($activeTheme): ?>
            <div class="features">
                <h2>Active Theme: <?= htmlspecialchars($activeTheme->getName()) ?></h2>
                <p style="color: #999; font-size: 12px; margin-bottom: 10px;">Theme is active and loaded successfully</p>
                <?php $config = $activeTheme->getConfig(); ?>
                <div class="feature-list">
                    <div class="feature-item">
                        <strong>Primary Color</strong>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 30px; height: 30px; background: <?= $config['colors']['primary'] ?>; border-radius: 4px; border: 1px solid #ddd;"></div>
                            <?= $config['colors']['primary'] ?>
                        </div>
                    </div>
                    <div class="feature-item">
                        <strong>Font Family</strong>
                        <?= htmlspecialchars(explode(',', $config['fonts']['body'])[0]) ?>
                    </div>
                    <div class="feature-item">
                        <strong>Layout</strong>
                        <?= $config['layout']['sidebar'] ? 'With Sidebar' : 'Full Width' ?>
                    </div>
                    <div class="feature-item">
                        <strong>Dark Mode</strong>
                        <?= $config['features']['dark_mode'] ? '✓ Supported' : '✗ Not supported' ?>
                    </div>
                </div>
            </div>

            <div class="demo-section">
                <h2>See It In Action</h2>
                <p style="color: #666; margin-bottom: 20px;">
                    View the blog with the <?= htmlspecialchars($activeTheme->getName()) ?> theme applied
                </p>
                <a href="demo.php" class="btn">View Blog Demo →</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
