<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM locales WHERE id = ? AND visible_web = 1 AND estado = 'en_venta' LIMIT 1");
$stmt->execute([$id]);
$local = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$local) {
    header('Location: index.php');
    exit;
}

$fotos = json_decode($local['fotos'] ?? '[]', true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($local['titulo']) ?> - Barcelona Locales</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #f9fafb; color: #1f2937; }
        
        .header { background: linear-gradient(135deg, #d97706 0%, #92400e 100%); color: white; padding: 20px; }
        .header .container { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 24px; }
        .header a { color: white; text-decoration: none; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
        
        .gallery { background: white; border-radius: 12px; overflow: hidden; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .main-image { width: 100%; height: 500px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 64px; }
        .main-image img { width: 100%; height: 100%; object-fit: cover; }
        .thumbnails { display: flex; gap: 10px; padding: 15px; overflow-x: auto; }
        .thumbnail { width: 100px; height: 80px; border-radius: 6px; overflow: hidden; cursor: pointer; border: 2px solid transparent; }
        .thumbnail:hover { border-color: #d97706; }
        .thumbnail img { width: 100%; height: 100%; object-fit: cover; }
        
        .content { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        
        .main-content { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .title { font-size: 32px; font-weight: bold; margin-bottom: 10px; }
        .location { color: #6b7280; font-size: 18px; margin-bottom: 20px; }
        
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 30px 0; }
        .feature-box { background: #f9fafb; padding: 15px; border-radius: 8px; text-align: center; }
        .feature-box .icon { font-size: 32px; margin-bottom: 8px; }
        .feature-box .label { font-size: 12px; color: #6b7280; }
        .feature-box .value { font-size: 20px; font-weight: bold; color: #1f2937; }
        
        .description { line-height: 1.8; color: #4b5563; margin: 20px 0; }
        
        .sidebar { }
        .price-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; text-align: center; }
        .price { font-size: 42px; font-weight: bold; color: #d97706; margin-bottom: 20px; }
        .contact-btn { display: block; background: #d97706; color: white; padding: 15px; border-radius: 8px; text-decoration: none; font-weight: 600; text-align: center; margin-bottom: 10px; }
        .contact-btn:hover { background: #b45309; }
        
        .info-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .info-item { padding: 12px 0; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; }
        .info-item:last-child { border-bottom: none; }
        .info-label { color: #6b7280; }
        .info-value { font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🏢 Barcelona Locales</h1>
            <a href="index.php">← Volver al catálogo</a>
        </div>
    </div>
    
    <div class="container">
        <div class="gallery">
            <div class="main-image" id="mainImage">
                <?php if (!empty($fotos)): ?>
                    <img src="<?= htmlspecialchars($fotos[0]) ?>" alt="<?= htmlspecialchars($local['titulo']) ?>">
                <?php else: ?>
                    🏢
                <?php endif; ?>
            </div>
            <?php if (count($fotos) > 1): ?>
                <div class="thumbnails">
                    <?php foreach ($fotos as $foto): ?>
                        <div class="thumbnail" onclick="changeImage('<?= htmlspecialchars($foto) ?>')">
                            <img src="<?= htmlspecialchars($foto) ?>" alt="Foto">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="content">
            <div class="main-content">
                <h1 class="title"><?= htmlspecialchars($local['titulo']) ?></h1>
                <div class="location">📍 <?= htmlspecialchars($local['direccion']) ?>, <?= htmlspecialchars($local['barrio']) ?></div>
                
                <div class="features-grid">
                    <div class="feature-box">
                        <div class="icon">📐</div>
                        <div class="value"><?= number_format($local['metros_cuadrados']) ?></div>
                        <div class="label">Metros cuadrados</div>
                    </div>
                    <?php if ($local['altura_techo']): ?>
                        <div class="feature-box">
                            <div class="icon">📏</div>
                            <div class="value"><?= $local['altura_techo'] ?>m</div>
                            <div class="label">Altura techo</div>
                        </div>
                    <?php endif; ?>
                    <?php if ($local['num_banos']): ?>
                        <div class="feature-box">
                            <div class="icon">🚽</div>
                            <div class="value"><?= $local['num_banos'] ?></div>
                            <div class="label">Baños</div>
                        </div>
                    <?php endif; ?>
                    <?php if ($local['tiene_escaparate']): ?>
                        <div class="feature-box">
                            <div class="icon">🪟</div>
                            <div class="value">Sí</div>
                            <div class="label">Escaparate</div>
                        </div>
                    <?php endif; ?>
                    <?php if ($local['tiene_salida_humos']): ?>
                        <div class="feature-box">
                            <div class="icon">💨</div>
                            <div class="value">Sí</div>
                            <div class="label">Salida humos</div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h2 style="margin: 30px 0 15px 0;">Descripción</h2>
                <div class="description">
                    <?= nl2br(htmlspecialchars($local['descripcion'])) ?>
                </div>
            </div>
            
            <div class="sidebar">
                <div class="price-card">
                    <div class="price"><?= number_format($local['precio_venta'], 0, ',', '.') ?> €</div>
                    <a href="contacto.php?local=<?= $local['id'] ?>" class="contact-btn">📧 Solicitar Información</a>
                    <a href="tel:+34600000000" class="contact-btn" style="background: #10b981;">📱 Llamar Ahora</a>
                </div>
                
                <div class="info-card">
                    <h3 style="margin-bottom: 15px;">Información</h3>
                    <div class="info-item">
                        <span class="info-label">Referencia</span>
                        <span class="info-value">#<?= str_pad($local['id'], 4, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Barrio</span>
                        <span class="info-value"><?= htmlspecialchars($local['barrio']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Estado</span>
                        <span class="info-value">En Venta</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function changeImage(src) {
            document.querySelector('#mainImage img').src = src;
        }
    </script>
</body>
</html>
