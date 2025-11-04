<?php
require_once __DIR__ . '/../../vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create test user if not exists
$email = 'admin@example.com';
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);

if (!$stmt->fetch()) {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $stmt->execute([
        'name' => 'Admin User',
        'email' => $email,
        'password' => $password
    ]);
    echo "✅ Usuario de prueba creado:\n";
} else {
    echo "ℹ️  Usuario de prueba ya existe\n";
}

echo "\n🔑 Credenciales:\n";
echo "   Email: admin@example.com\n";
echo "   Password: admin123\n";
echo "\n🚀 Ahora puedes usar la API en: http://localhost:8000/examples/17-rest-api/\n";
