<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');

// Get filters
$barrio = $_GET['barrio'] ?? '';
$precioMax = $_GET['precio_max'] ?? '';
$metrosMin = $_GET['metros_min'] ?? '';

// Build query
$sql = "SELECT * FROM locales WHERE visible_web = 1 AND estado = 'en_venta'";
$params = [];

if ($barrio) {
    $sql .= " AND barrio = :barrio";
    $params['barrio'] = $barrio;
}
if ($precioMax) {
    $sql .= " AND precio_venta <= :precio_max";
    $params['precio_max'] = $precioMax;
}
if ($metrosMin) {
    $sql .= " AND metros_cuadrados >= :metros_min";
    $params['metros_min'] = $metrosMin;
}

$sql .= " ORDER BY destacado DESC, created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$locales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique barrios for filter
$barrios = $pdo->query("SELECT DISTINCT barrio FROM locales WHERE visible_web = 1 AND estado = 'en_venta' ORDER BY barrio")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcelona Locales - Locales Comerciales en Barcelona</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #f9fafb; color: #1f2937; }
        
        .header { background: linear-gradient(135deg, #d97706 0%, #92400e 100%); color: white; padding: 30px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header .container { max-width: 1200px; margin: 0 auto; }
        .header h1 { font-size: 32px; margin-bottom: 5px; }
        .header p { opacity: 0.9; font-size: 16px; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .filters { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .filters form { display: flex; gap: 15px; flex-wrap: wrap; align-items: end; }
        .filter-group { flex: 1; min-width: 200px; }
        .filter-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #374151; }
        .filter-group select, .filter-group input { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .filter-group button { background: #d97706; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 500; }
        .filter-group button:hover { background: #b45309; }
        
        .stats { display: flex; gap: 20px; margin: 20px 0; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; flex: 1; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-card .value { font-size: 32px; font-weight: bold; color: #d97706; }
        .stat-card .label { color: #6b7280; font-size: 14px; margin-top: 5px; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; margin: 30px 0; }
        
        .card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s; }
        .card:hover { transform: translateY(-4px); box-shadow: 0 8px 16px rgba(0,0,0,0.15); }
        .card.destacado { border: 3px solid #d97706; }
        
        .card-image { width: 100%; height: 220px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 48px; position: relative; }
        .card-image img { width: 100%; height: 100%; object-fit: cover; }
        .badge { position: absolute; top: 10px; right: 10px; background: #d97706; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        
        .card-content { padding: 20px; }
        .card-title { font-size: 20px; font-weight: 600; margin-bottom: 10px; color: #1f2937; }
        .card-location { color: #6b7280; font-size: 14px; margin-bottom: 15px; display: flex; align-items: center; gap: 5px; }
        
        .card-features { display: flex; gap: 15px; margin: 15px 0; padding: 15px 0; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; }
        .feature { display: flex; align-items: center; gap: 5px; font-size: 14px; color: #4b5563; }
        .feature strong { color: #1f2937; }
        
        .card-price { font-size: 28px; font-weight: bold; color: #d97706; margin: 15px 0; }
        .card-actions { display: flex; gap: 10px; }
        .btn { padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 14px; text-align: center; flex: 1; }
        .btn-primary { background: #d97706; color: white; }
        .btn-primary:hover { background: #b45309; }
        .btn-secondary { background: #f3f4f6; color: #374151; }
        .btn-secondary:hover { background: #e5e7eb; }
        
        .empty { text-align: center; padding: 60px 20px; }
        .empty-icon { font-size: 64px; margin-bottom: 20px; }
        .empty-text { font-size: 18px; color: #6b7280; }
        
        .footer { background: #1f2937; color: white; padding: 40px 20px; margin-top: 60px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🏢 Barcelona Locales</h1>
            <p>Locales comerciales en las mejores zonas de Barcelona</p>
        </div>
    </div>
    
    <div class="container">
        <div class="filters">
            <form method="GET">
                <div class="filter-group">
                    <label>Barrio</label>
                    <select name="barrio">
                        <option value="">Todos los barrios</option>
                        <?php foreach ($barrios as $b): ?>
                            <option value="<?= htmlspecialchars($b) ?>" <?= $barrio === $b ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Precio máximo (€)</label>
                    <input type="number" name="precio_max" value="<?= htmlspecialchars($precioMax) ?>" placeholder="Ej: 300000">
                </div>
                <div class="filter-group">
                    <label>Metros mínimos (m²)</label>
                    <input type="number" name="metros_min" value="<?= htmlspecialchars($metrosMin) ?>" placeholder="Ej: 80">
                </div>
                <div class="filter-group">
                    <button type="submit">🔍 Buscar</button>
                </div>
            </form>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="value"><?= count($locales) ?></div>
                <div class="label">Locales Disponibles</div>
            </div>
            <div class="stat-card">
                <div class="value"><?= count($barrios) ?></div>
                <div class="label">Barrios</div>
            </div>
        </div>
        
        <?php if (empty($locales)): ?>
            <div class="empty">
                <div class="empty-icon">🔍</div>
                <div class="empty-text">No se encontraron locales con estos criterios</div>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($locales as $local): ?>
                    <?php
                    $fotos = json_decode($local['fotos'] ?? '[]', true);
                    $primeraFoto = !empty($fotos) ? $fotos[0] : null;
                    ?>
                    <div class="card <?= $local['destacado'] ? 'destacado' : '' ?>">
                        <div class="card-image">
                            <?php if ($primeraFoto): ?>
                                <img src="<?= htmlspecialchars($primeraFoto) ?>" alt="<?= htmlspecialchars($local['titulo']) ?>">
                            <?php else: ?>
                                🏢
                            <?php endif; ?>
                            <?php if ($local['destacado']): ?>
                                <span class="badge">⭐ DESTACADO</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?= htmlspecialchars($local['titulo']) ?></h3>
                            <div class="card-location">
                                📍 <?= htmlspecialchars($local['direccion']) ?>, <?= htmlspecialchars($local['barrio']) ?>
                            </div>
                            
                            <div class="card-features">
                                <div class="feature">
                                    <span>📐</span>
                                    <strong><?= number_format($local['metros_cuadrados']) ?></strong> m²
                                </div>
                                <?php if ($local['num_banos']): ?>
                                    <div class="feature">
                                        <span>🚽</span>
                                        <strong><?= $local['num_banos'] ?></strong> baños
                                    </div>
                                <?php endif; ?>
                                <?php if ($local['tiene_escaparate']): ?>
                                    <div class="feature">
                                        <span>🪟</span>
                                        Escaparate
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-price">
                                <?= number_format($local['precio_venta'], 0, ',', '.') ?> €
                            </div>
                            
                            <div class="card-actions">
                                <a href="detalle.php?id=<?= $local['id'] ?>" class="btn btn-primary">Ver Detalles</a>
                                <a href="contacto.php?local=<?= $local['id'] ?>" class="btn btn-secondary">Contactar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <p><strong>Barcelona Locales</strong> - Especialistas en locales comerciales</p>
        <p style="margin-top: 10px; opacity: 0.8;">📧 info@barcelonalocales.com | 📱 +34 600 000 000</p>
    </div>
</body>
</html>
