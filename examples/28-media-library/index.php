<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\Media\MediaLibrary;
use DynamicCRUD\Media\MediaBrowser;
use DynamicCRUD\Media\ImageEditor;

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize media library
$uploadDir = __DIR__ . '/uploads';
$baseUrl = '/examples/28-media-library/uploads';
$library = new MediaLibrary($pdo, $uploadDir, $baseUrl);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_GET['action']) && $_GET['action'] === 'upload') {
        $results = [];
        
        foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
            $file = [
                'name' => $_FILES['files']['name'][$index],
                'type' => $_FILES['files']['type'][$index],
                'tmp_name' => $tmpName,
                'error' => $_FILES['files']['error'][$index],
                'size' => $_FILES['files']['size'][$index],
            ];
            
            $folder = $_GET['folder'] ?? '/';
            $result = $library->upload($file, $folder);
            $results[] = $result;
        }
        
        echo json_encode(['success' => true, 'results' => $results]);
        exit;
    }
}

// Handle GET actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'delete':
            $id = (int) $_GET['id'];
            $library->delete($id);
            header('Location: index.php');
            exit;
            
        case 'create_folder':
            $name = $_GET['name'] ?? '';
            if ($name) {
                $library->createFolder($name);
            }
            header('Location: index.php');
            exit;
            
        case 'thumbnail':
            $id = (int) $_GET['id'];
            $file = $library->getFile($id);
            
            if ($file && strpos($file['mime_type'], 'image/') === 0) {
                $editor = new ImageEditor();
                $thumbPath = $uploadDir . '/thumbs/' . basename($file['filepath']);
                
                if (!is_dir($uploadDir . '/thumbs')) {
                    mkdir($uploadDir . '/thumbs', 0755, true);
                }
                
                if (!file_exists($thumbPath)) {
                    $editor->thumbnail($file['filepath'], $thumbPath, 150);
                }
                
                header('Content-Type: ' . $file['mime_type']);
                readfile($thumbPath);
                exit;
            }
            break;
    }
}

// Render browser
$folder = $_GET['folder'] ?? '/';
$browser = new MediaBrowser($library);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library - DynamicCRUD</title>
</head>
<body style="margin: 0; padding: 0;">
    <?= $browser->render('grid', $folder) ?>
</body>
</html>
