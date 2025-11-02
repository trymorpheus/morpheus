<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

// Conexión a la base de datos
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Crear instancia de DynamicCRUD para la tabla posts
$crud = new DynamicCRUD($pdo, 'posts');

// Configurar relación M:N con UI avanzada (checkboxes con búsqueda)
$crud->addManyToMany(
    'tags',              // Field name
    'post_tags',         // Pivot table
    'post_id',           // Local key
    'tag_id',            // Foreign key
    'tags',              // Related table
    'checkboxes'         // UI type: 'checkboxes' or 'select'
);

// Manejar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        echo '<div class="success-message">✅ Post guardado exitosamente! ID: ' . $result['id'] . '</div>';
        echo '<p><a href="?">Crear otro post</a> | <a href="?id=' . $result['id'] . '">Editar este post</a></p>';
    } else {
        echo '<div class="error-message">❌ Error: ';
        if (isset($result['errors'])) {
            echo '<ul>';
            foreach ($result['errors'] as $field => $error) {
                echo '<li><strong>' . htmlspecialchars($field) . ':</strong> ' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
        } else {
            echo htmlspecialchars($result['error']);
        }
        echo '</div>';
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced M:N UI Demo - DynamicCRUD</title>
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
        
        .error-message ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-box h3 {
            margin-top: 0;
            color: #1976D2;
        }
        
        .info-box ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        
        .comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .comparison-item {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .comparison-item h4 {
            margin-top: 0;
            color: #333;
        }
        
        .comparison-item.old {
            background: #fff3cd;
        }
        
        .comparison-item.new {
            background: #d4edda;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 Advanced Many-to-Many UI</h1>
        <p class="subtitle">Interfaz moderna con checkboxes y búsqueda para relaciones M:N</p>
        
        <div class="info-box">
            <h3>✨ Características de la Nueva UI</h3>
            <ul>
                <li><strong>Checkboxes</strong> - Más intuitivo que &lt;select multiple&gt;</li>
                <li><strong>Búsqueda en tiempo real</strong> - Filtra opciones mientras escribes</li>
                <li><strong>Seleccionar/Limpiar todo</strong> - Botones de acción rápida</li>
                <li><strong>Contador</strong> - Muestra cuántos elementos están seleccionados</li>
                <li><strong>Scroll</strong> - Maneja listas largas sin problemas</li>
                <li><strong>Accesible</strong> - Labels clickeables, navegación por teclado</li>
            </ul>
        </div>
        
        <?php echo $crud->renderForm($_GET['id'] ?? null); ?>
        
        <div class="comparison">
            <div class="comparison-item old">
                <h4>❌ UI Antigua (select multiple)</h4>
                <ul>
                    <li>Requiere Ctrl+Click</li>
                    <li>No intuitivo</li>
                    <li>Sin búsqueda</li>
                    <li>Difícil de usar</li>
                </ul>
            </div>
            <div class="comparison-item new">
                <h4>✅ UI Nueva (checkboxes)</h4>
                <ul>
                    <li>Click simple</li>
                    <li>Muy intuitivo</li>
                    <li>Con búsqueda</li>
                    <li>Fácil de usar</li>
                </ul>
            </div>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <h3>💡 Cómo usar</h3>
            <p>Para activar la UI avanzada, simplemente especifica el tipo de UI al configurar la relación:</p>
            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>$crud->addManyToMany(
    'tags',
    'post_tags',
    'post_id',
    'tag_id',
    'tags',
    'checkboxes'  // 👈 UI type: 'checkboxes' o 'select'
);</code></pre>
        </div>
    </div>
</body>
</html>
