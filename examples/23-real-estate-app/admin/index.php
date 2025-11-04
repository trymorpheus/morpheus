<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use DynamicCRUD\Admin\AdminPanel;
use DynamicCRUD\GlobalMetadata;

session_start();

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');

// Setup branding
$config = new GlobalMetadata($pdo);
if (!$config->has('branding.app_name')) {
    $config->set('branding.app_name', 'Barcelona Locales - Admin');
    $config->set('branding.colors', [
        'primary' => '#d97706',
        'secondary' => '#92400e',
        'background' => '#ffffff',
        'text' => '#1f2937'
    ]);
}

$admin = new AdminPanel($pdo, [
    'title' => 'Barcelona Locales - Panel de Administración',
    'theme' => [
        'primary' => '#d97706',
        'sidebar_bg' => '#1f2937',
        'sidebar_text' => '#f3f4f6'
    ]
]);

$admin->addTable('locales', ['icon' => '🏢', 'label' => 'Locales Comerciales']);
$admin->addTable('consultas', ['icon' => '📧', 'label' => 'Consultas']);

echo $admin->render();
