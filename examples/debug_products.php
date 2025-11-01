<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<h2>Debug POST Data</h2>";
echo "<pre>";
echo "POST: ";
print_r($_POST);
echo "\nFILES: ";
print_r($_FILES);
echo "\nSESSION: ";
session_start();
print_r($_SESSION);
echo "</pre>";

$cache = new FileCacheStrategy();
$crud = new DynamicCRUD($pdo, 'products', $cache);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Procesando formulario...</h3>";
    try {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        $result = $crud->handleSubmission();
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    } catch (\Exception $e) {
        echo "<p style='color: red;'>EXCEPTION: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Debug - Productos</title>
</head>
<body>
    <h1>Debug Productos</h1>
    <?= $crud->renderForm($id) ?>
</body>
</html>
