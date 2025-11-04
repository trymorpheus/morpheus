<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    $allowedRoles = ['admin', 'manager', 'warehouse', 'guest'];
    $role = $_POST['role'];
    
    if (in_array($role, $allowedRoles)) {
        $_SESSION['role'] = $role;
    }
}

// Conservar query string completa
$queryString = $_SERVER['QUERY_STRING'] ?? '';
$redirect = 'index.php' . ($queryString ? '?' . $queryString : '');

header('Location: ' . $redirect);
exit;
