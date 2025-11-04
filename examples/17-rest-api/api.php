<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\API\RestAPIGenerator;

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create API instance
$api = new RestAPIGenerator($pdo, 'your-secret-key-here', [
    'prefix' => '/examples/17-rest-api/api.php',
    'version' => 'v1',
    'cors' => true
]);

// Handle request
$api->handleRequest();
