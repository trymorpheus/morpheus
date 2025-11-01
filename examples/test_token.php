<?php
session_start();

echo "<h2>Test Token CSRF</h2>";
echo "<pre>";
echo "SESSION:\n";
print_r($_SESSION);
echo "\nPOST:\n";
print_r($_POST);
echo "</pre>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenForm = $_POST['csrf_token'] ?? 'NO_TOKEN';
    $tokenSession = $_SESSION['csrf_token'] ?? 'NO_SESSION';
    
    echo "<p><strong>Token del formulario:</strong> $tokenForm</p>";
    echo "<p><strong>Token de la sesión:</strong> $tokenSession</p>";
    echo "<p><strong>¿Coinciden?:</strong> " . ($tokenForm === $tokenSession ? 'SÍ ✓' : 'NO ✗') . "</p>";
}

require_once __DIR__ . '/../vendor/autoload.php';
use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$cache = new FileCacheStrategy();
$crud = new DynamicCRUD($pdo, 'products', $cache);

echo "<h3>Formulario</h3>";
echo $crud->renderForm();
