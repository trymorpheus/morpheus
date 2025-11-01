<?php
session_start();

echo "<h2>🔍 Debug CSRF Token</h2>";

// Mostrar estado de la sesión
echo "<h3>1. Estado de la Sesión</h3>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE') . "\n";
echo "Session Data:\n";
print_r($_SESSION);
echo "</pre>";

// Mostrar datos POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>2. Datos POST Recibidos</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h3>3. Comparación de Tokens</h3>";
    $tokenPost = $_POST['csrf_token'] ?? 'NO_EXISTE';
    $tokenSession = $_SESSION['csrf_token'] ?? 'NO_EXISTE';
    
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Origen</th><th>Token</th></tr>";
    echo "<tr><td>POST</td><td><code>$tokenPost</code></td></tr>";
    echo "<tr><td>SESSION</td><td><code>$tokenSession</code></td></tr>";
    echo "<tr><td><strong>¿Coinciden?</strong></td><td><strong>" . ($tokenPost === $tokenSession ? '✅ SÍ' : '❌ NO') . "</strong></td></tr>";
    echo "</table>";
}

// Cargar DynamicCRUD
require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$cache = new FileCacheStrategy();
$crud = new DynamicCRUD($pdo, 'products', $cache);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>4. Resultado de handleSubmission()</h3>";
    echo "<pre>";
    $result = $crud->handleSubmission();
    print_r($result);
    echo "</pre>";
}

// Renderizar formulario
echo "<h3>5. Formulario Generado</h3>";
$id = $_GET['id'] ?? null;
$formHtml = $crud->renderForm($id);

// Extraer el token del HTML generado
preg_match('/name="csrf_token" value="([^"]+)"/', $formHtml, $matches);
$tokenInHtml = $matches[1] ?? 'NO_ENCONTRADO';

echo "<p><strong>Token en el HTML del formulario:</strong> <code>$tokenInHtml</code></p>";
echo "<p><strong>Token en SESSION después de renderForm:</strong> <code>" . ($_SESSION['csrf_token'] ?? 'NO_EXISTE') . "</code></p>";

echo $formHtml;
?>
