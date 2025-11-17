<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create CRUD instance
$crud = new Morpheus($pdo, 'properties');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        header('Location: ?success=1');
        exit;
    } else {
        $error = $result['error'] ?? 'Error al guardar';
        $errors = $result['errors'] ?? [];
    }
}

// Get property ID for editing
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏠 Inmobiliaria - Múltiples Fotos</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 32px; }
        .header p { margin: 10px 0 0; opacity: 0.9; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .actions { margin-bottom: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .btn:hover { background: #5568d3; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .properties-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
        .property-card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .property-card img { width: 100%; height: 200px; object-fit: cover; }
        .property-card .content { padding: 20px; }
        .property-card h3 { margin: 0 0 10px; font-size: 18px; }
        .property-card .price { font-size: 24px; font-weight: bold; color: #667eea; margin: 10px 0; }
        .property-card .details { color: #666; font-size: 14px; margin: 10px 0; }
        .property-card .status { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .status.available { background: #d4edda; color: #155724; }
        .status.sold { background: #f8d7da; color: #721c24; }
        .status.rented { background: #fff3cd; color: #856404; }
        .property-card .actions { margin-top: 15px; display: flex; gap: 10px; }
        .property-card .btn { padding: 8px 16px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 Inmobiliaria - Gestión de Propiedades</h1>
            <p>Ejemplo de subida múltiple de archivos (fotos de propiedades)</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success">
                ✅ Propiedad guardada correctamente con todas las fotos!
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="actions">
            <?php if ($id): ?>
                <a href="?" class="btn btn-secondary">← Volver al listado</a>
            <?php else: ?>
                <a href="?id=new" class="btn">➕ Nueva Propiedad</a>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['id'])): ?>
            <!-- Form -->
            <?= $crud->renderForm($id === 'new' ? null : $id) ?>
        <?php else: ?>
            <!-- List -->
            <?php
            $properties = $pdo->query("SELECT * FROM properties ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <div class="properties-grid">
                <?php foreach ($properties as $property): ?>
                    <?php
                    $photos = json_decode($property['photos'] ?? '[]', true);
                    $firstPhoto = !empty($photos) ? $photos[0] : 'https://via.placeholder.com/300x200?text=Sin+Foto';
                    $statusLabels = ['available' => 'Disponible', 'sold' => 'Vendida', 'rented' => 'Alquilada'];
                    ?>
                    <div class="property-card">
                        <img src="<?= htmlspecialchars($firstPhoto) ?>" alt="<?= htmlspecialchars($property['title']) ?>">
                        <div class="content">
                            <h3><?= htmlspecialchars($property['title']) ?></h3>
                            <div class="price"><?= number_format($property['price'], 0, ',', '.') ?> €</div>
                            <div class="details">
                                🛏️ <?= $property['bedrooms'] ?> hab. | 
                                🛿 <?= $property['bathrooms'] ?> baños | 
                                📏 <?= $property['area'] ?> m²
                            </div>
                            <div class="details">
                                📍 <?= htmlspecialchars($property['city']) ?>
                            </div>
                            <div class="details">
                                📷 <?= count($photos) ?> fotos
                            </div>
                            <span class="status <?= $property['status'] ?>">
                                <?= $statusLabels[$property['status']] ?>
                            </span>
                            <div class="actions">
                                <a href="?id=<?= $property['id'] ?>" class="btn">Editar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
