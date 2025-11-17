<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\GlobalMetadata;
use Morpheus\BrandingManager;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');

$config = new GlobalMetadata($pdo);
$branding = new BrandingManager($config);

// Configure branding (only once)
if (!$config->has('branding.app_name')) {
    $config->set('branding.app_name', 'My Awesome App');
    $config->set('branding.logo', 'https://via.placeholder.com/150x40?text=Logo');
    $config->set('branding.favicon', 'https://via.placeholder.com/32x32?text=F');
    
    $config->set('branding.colors', [
        'primary' => '#667eea',
        'secondary' => '#764ba2',
        'background' => '#ffffff',
        'text' => '#2d3748'
    ]);
    
    $config->set('branding.fonts', [
        'family' => 'Inter, system-ui, sans-serif',
        'size' => '16px'
    ]);
    
    $config->set('branding.layout', [
        'max_width' => '1200px',
        'padding' => '20px',
        'border_radius' => '8px'
    ]);
    
    $config->set('branding.dark_mode', true);
    
    $config->set('branding.navigation', [
        'position' => 'top',
        'items' => [
            ['label' => 'Home', 'url' => '/', 'icon' => '🏠'],
            ['label' => 'Users', 'url' => '/users', 'icon' => '👥'],
            ['label' => 'Settings', 'url' => '/settings', 'icon' => '⚙️']
        ]
    ]);
    
    $config->set('branding.custom_css', '
        .brand-nav { background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary)); padding: 15px; }
        .brand-nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 20px; }
        .brand-nav a { color: white; text-decoration: none; font-weight: 500; }
        .brand-nav a:hover { opacity: 0.8; }
    ');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($branding->getAppName()) ?></title>
    <?= $branding->renderBranding() ?>
</head>
<body>
    <div class="container">
        <header style="margin-bottom: 30px;">
            <?= $branding->renderLogo() ?>
        </header>
        
        <?= $branding->renderNavigation() ?>
        
        <main style="margin-top: 30px;">
            <h2>Welcome to <?= htmlspecialchars($branding->getAppName()) ?>!</h2>
            
            <div style="background: var(--brand-background, #f7fafc); padding: 20px; border-radius: var(--brand-border-radius, 8px); margin: 20px 0;">
                <h3>Branding Configuration</h3>
                <p>This example demonstrates the enhanced branding capabilities:</p>
                <ul>
                    <li>✅ Custom app name and logo</li>
                    <li>✅ Custom colors (primary, secondary, background, text)</li>
                    <li>✅ Custom fonts (family, size)</li>
                    <li>✅ Layout configuration (max-width, padding, border-radius)</li>
                    <li>✅ Dark mode support (automatic based on system preference)</li>
                    <li>✅ Custom navigation</li>
                    <li>✅ Custom CSS injection</li>
                    <li>✅ Favicon support</li>
                </ul>
            </div>
            
            <div style="background: var(--brand-primary, #667eea); color: white; padding: 20px; border-radius: var(--brand-border-radius, 8px); margin: 20px 0;">
                <h3>Primary Color Example</h3>
                <p>This box uses the primary brand color.</p>
            </div>
            
            <div style="background: var(--brand-secondary, #764ba2); color: white; padding: 20px; border-radius: var(--brand-border-radius, 8px); margin: 20px 0;">
                <h3>Secondary Color Example</h3>
                <p>This box uses the secondary brand color.</p>
            </div>
            
            <h3>CLI Commands</h3>
            <pre style="background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 8px; overflow-x: auto;">
# Set branding configuration
php bin/morpheus branding:set app_name "My App"
php bin/morpheus branding:set logo "/path/to/logo.png"
php bin/morpheus branding:set primary_color "#667eea"
php bin/morpheus branding:set font_family "Inter, sans-serif"
php bin/morpheus branding:set max_width "1400px"

# Show current branding
php bin/morpheus branding:show
            </pre>
            
            <h3>PHP Usage</h3>
            <pre style="background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 8px; overflow-x: auto;">
$config = new GlobalMetadata($pdo);
$branding = new BrandingManager($config);

// Render all branding (favicon, CSS variables, custom CSS)
echo $branding->renderBranding();

// Render logo
echo $branding->renderLogo();

// Render navigation
echo $branding->renderNavigation();

// Get configuration
$appName = $branding->getAppName();
$colors = $branding->getColors();
$fonts = $branding->getFonts();
$layout = $branding->getLayout();
            </pre>
            
            <p style="margin-top: 30px; color: var(--brand-text, #718096);">
                <strong>Try switching to dark mode!</strong> If your system is set to dark mode, 
                you'll see the colors automatically adjust.
            </p>
        </main>
    </div>
</body>
</html>
