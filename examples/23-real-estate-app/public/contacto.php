<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');

$localId = $_GET['local'] ?? null;
$local = null;

if ($localId) {
    $stmt = $pdo->prepare("SELECT id, titulo FROM locales WHERE id = ? LIMIT 1");
    $stmt->execute([$localId]);
    $local = $stmt->fetch(PDO::FETCH_ASSOC);
}

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crud = new DynamicCRUD($pdo, 'consultas');
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $message = ['type' => 'success', 'text' => '¡Gracias! Hemos recibido tu consulta. Te contactaremos pronto.'];
    } else {
        $message = ['type' => 'error', 'text' => 'Hubo un error. Por favor, inténtalo de nuevo.'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Barcelona Locales</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #f9fafb; color: #1f2937; }
        
        .header { background: linear-gradient(135deg, #d97706 0%, #92400e 100%); color: white; padding: 20px; }
        .header .container { max-width: 800px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 24px; }
        .header a { color: white; text-decoration: none; }
        
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .card h2 { font-size: 28px; margin-bottom: 10px; }
        .card p { color: #6b7280; margin-bottom: 30px; }
        
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .message.success { background: #d1fae5; color: #065f46; }
        .message.error { background: #fee2e2; color: #991b1b; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #374151; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 16px; font-family: inherit; }
        .form-group textarea { resize: vertical; min-height: 120px; }
        
        .btn { background: #d97706; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; width: 100%; }
        .btn:hover { background: #b45309; }
        
        .info-box { background: #fef3c7; border-left: 4px solid #d97706; padding: 20px; border-radius: 8px; margin-top: 30px; }
        .info-box h3 { margin-bottom: 10px; color: #92400e; }
        .info-box p { color: #78350f; margin: 5px 0; }
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
        <div class="card">
            <h2>Solicitar Información</h2>
            <p>Completa el formulario y nos pondremos en contacto contigo lo antes posible.</p>
            
            <?php if ($message): ?>
                <div class="message <?= $message['type'] ?>">
                    <?= htmlspecialchars($message['text']) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <?php if ($local): ?>
                    <div class="form-group">
                        <label>Local de Interés</label>
                        <input type="text" value="<?= htmlspecialchars($local['titulo']) ?>" readonly style="background: #f3f4f6;">
                        <input type="hidden" name="local_id" value="<?= $local['id'] ?>">
                    </div>
                <?php else: ?>
                    <input type="hidden" name="local_id" value="">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Nombre Completo *</label>
                    <input type="text" name="nombre" required placeholder="Tu nombre">
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required placeholder="tu@email.com">
                </div>
                
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" placeholder="+34 600 000 000">
                </div>
                
                <div class="form-group">
                    <label>Mensaje</label>
                    <textarea name="mensaje" placeholder="Cuéntanos qué necesitas..."></textarea>
                </div>
                
                <button type="submit" class="btn">📧 Enviar Consulta</button>
            </form>
            
            <div class="info-box">
                <h3>📞 Otras formas de contacto</h3>
                <p><strong>Teléfono:</strong> +34 600 000 000</p>
                <p><strong>Email:</strong> info@barcelonalocales.com</p>
                <p><strong>Horario:</strong> Lunes a Viernes, 9:00 - 18:00</p>
            </div>
        </div>
    </div>
</body>
</html>
