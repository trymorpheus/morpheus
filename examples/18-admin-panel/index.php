<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\Admin\AdminPanel;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$admin = new AdminPanel($pdo, [
    'title' => 'Mi Admin Panel',
    'theme' => [
        'primary' => '#667eea',
        'sidebar_bg' => '#2d3748',
        'sidebar_text' => '#e2e8f0'
    ]
]);

$admin->addTable('users', ['icon' => '👥', 'label' => 'Usuarios']);
$admin->addTable('posts', ['icon' => '📝', 'label' => 'Posts']);
$admin->addTable('categories', ['icon' => '📁', 'label' => 'Categorías']);
$admin->addTable('tags', ['icon' => '🏷️', 'label' => 'Tags']);

echo $admin->render();
