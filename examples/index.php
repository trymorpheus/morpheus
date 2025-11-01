<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

// Configuración de base de datos
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Crear instancia de DynamicCRUD para tabla 'users'
$crud = new DynamicCRUD($pdo, 'users');

// Procesar envío de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();

    if ($result['success']) {
        $action = isset($_POST['id']) ? 'actualizado' : 'creado';
        echo "<p style='color: green;'>✓ Usuario {$action} con ID: {$result['id']}</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . ($result['error'] ?? 'Validación fallida') . "</p>";
        if (isset($result['errors'])) {
            echo "<ul>";
            foreach ($result['errors'] as $field => $errors) {
                foreach ($errors as $error) {
                    echo "<li>{$error}</li>";
                }
            }
            echo "</ul>";
        }
    }
}

// Obtener lista de usuarios
$stmt = $pdo->query('SELECT id, name, email FROM users ORDER BY id DESC');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Renderizar formulario
$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DynamicCRUD - Ejemplo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
        }

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f8f9fa;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        h2 {
            margin-top: 0;
        }
    </style>
</head>

<body>
    <h1>DynamicCRUD - Gestión de Usuarios</h1>
    <div class="container">
        <div>
            <h2><?= $id ? 'Editar Usuario' : 'Nuevo Usuario' ?></h2>
            <?= $crud->renderForm($id) ?>
        </div>
        <div>
            <h2>Lista de Usuarios</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><a href="?id=<?= $user['id'] ?>">Editar</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p><a href="index.php">← Crear nuevo usuario</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>