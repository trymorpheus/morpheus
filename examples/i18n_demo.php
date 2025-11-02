<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\I18n\Translator;

// Detect or set locale
$locale = $_GET['lang'] ?? Translator::detectLocale();
$availableLocales = ['en', 'es', 'fr'];

if (!in_array($locale, $availableLocales)) {
    $locale = 'en';
}

// Conexión a la base de datos
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Crear instancia de DynamicCRUD con locale
$crud = new DynamicCRUD($pdo, 'users', null, $locale);

// Configurar M:N con locale
$crud->addManyToMany('tags', 'post_tags', 'post_id', 'tag_id', 'tags');

// Manejar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    $translator = $crud->getTranslator();
    
    if ($result['success']) {
        $message = $translator->t('success.created');
        echo '<div class="success-message">✅ ' . htmlspecialchars($message) . '</div>';
        echo '<p><a href="?lang=' . $locale . '">Crear otro</a></p>';
    } else {
        $errorMsg = $translator->t('error.database', ['message' => $result['error'] ?? '']);
        echo '<div class="error-message">❌ ' . htmlspecialchars($errorMsg) . '</div>';
    }
}

?>
<!DOCTYPE html>
<html lang="<?php echo $locale; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>i18n Demo - DynamicCRUD</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .lang-switcher {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .lang-switcher a {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 5px;
            background: white;
            color: #2196F3;
            text-decoration: none;
            border-radius: 4px;
            border: 2px solid #2196F3;
            font-weight: 500;
        }
        
        .lang-switcher a.active {
            background: #2196F3;
            color: white;
        }
        
        .lang-switcher a:hover {
            background: #1976D2;
            border-color: #1976D2;
            color: white;
        }
        
        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-box h3 {
            margin-top: 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌍 Internationalization (i18n) Demo</h1>
        <p class="subtitle">Sistema de traducciones multi-idioma</p>
        
        <div class="lang-switcher">
            <strong>🌐 Language / Idioma / Langue:</strong><br><br>
            <a href="?lang=en" class="<?php echo $locale === 'en' ? 'active' : ''; ?>">🇬🇧 English</a>
            <a href="?lang=es" class="<?php echo $locale === 'es' ? 'active' : ''; ?>">🇪🇸 Español</a>
            <a href="?lang=fr" class="<?php echo $locale === 'fr' ? 'active' : ''; ?>">🇫🇷 Français</a>
        </div>
        
        <div class="info-box">
            <h3>✨ <?php echo $locale === 'es' ? 'Características' : ($locale === 'fr' ? 'Caractéristiques' : 'Features'); ?></h3>
            <ul>
                <li><?php echo $locale === 'es' ? 'Detección automática de idioma del navegador' : ($locale === 'fr' ? 'Détection automatique de la langue du navigateur' : 'Automatic browser language detection'); ?></li>
                <li><?php echo $locale === 'es' ? 'Traducciones para validaciones y errores' : ($locale === 'fr' ? 'Traductions pour validations et erreurs' : 'Translations for validations and errors'); ?></li>
                <li><?php echo $locale === 'es' ? 'Soporte para múltiples idiomas' : ($locale === 'fr' ? 'Support multi-langues' : 'Multi-language support'); ?></li>
                <li><?php echo $locale === 'es' ? 'Fácil de extender con nuevos idiomas' : ($locale === 'fr' ? 'Facile à étendre avec de nouvelles langues' : 'Easy to extend with new languages'); ?></li>
            </ul>
        </div>
        
        <?php echo $crud->renderForm($_GET['id'] ?? null); ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <h3>💡 <?php echo $locale === 'es' ? 'Uso' : ($locale === 'fr' ? 'Utilisation' : 'Usage'); ?></h3>
            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>// <?php echo $locale === 'es' ? 'Crear con locale específico' : ($locale === 'fr' ? 'Créer avec locale spécifique' : 'Create with specific locale'); ?>

$crud = new DynamicCRUD($pdo, 'users', null, 'es');

// <?php echo $locale === 'es' ? 'O cambiar locale después' : ($locale === 'fr' ? 'Ou changer locale après' : 'Or change locale later'); ?>

$crud->setLocale('fr');

// <?php echo $locale === 'es' ? 'Detección automática' : ($locale === 'fr' ? 'Détection automatique' : 'Auto-detection'); ?>

$locale = Translator::detectLocale();
$crud = new DynamicCRUD($pdo, 'users', null, $locale);</code></pre>
        </div>
    </div>
</body>
</html>
