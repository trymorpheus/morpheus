<?php
/**
 * DynamicCRUD - File Upload Example
 * 
 * Secure file upload handling with:
 * - Real MIME type validation (finfo)
 * - File size checks
 * - Unique filename generation
 * - Automatic form encoding
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;
use Morpheus\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cache = new FileCacheStrategy();
$uploadDir = __DIR__ . '/../uploads';
$crud = new Morpheus($pdo, 'products', $cache, $uploadDir);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $action = isset($_POST['id']) ? 'updated' : 'created';
        header("Location: ?success=Product $action with ID: {$result['id']}");
        exit;
    } else {
        $error = $result['error'] ?? 'Validation failed';
        $errors = $result['errors'] ?? [];
    }
}

$stmt = $pdo->query('SELECT p.id, p.name, p.price, p.image, c.name as category 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id
                     ORDER BY p.id DESC LIMIT 10');
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Uploads - DynamicCRUD</title>
    <link rel="stylesheet" href="../assets/dynamiccrud.css">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .badge { background: #dc3545; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .info-box { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .product-img { max-width: 60px; height: auto; border-radius: 4px; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <h1>📁 File Upload Handling</h1>
    <span class="badge">Secure Upload</span>
    <p style="color: #666; margin: 10px 0 20px 0;">
        Automatic file upload with MIME validation, size checks, and unique filenames.
    </p>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <div class="info-box">
        <strong>🔒 Security Features:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            <li><strong>Real MIME detection</strong> - Uses <code>finfo</code>, not file extension</li>
            <li><strong>File size validation</strong> - Configurable max size via metadata</li>
            <li><strong>Unique filenames</strong> - Prevents overwrites and conflicts</li>
            <li><strong>Allowed types</strong> - Whitelist via <code>accept</code> metadata</li>
        </ul>
    </div>

    <div class="container">
        <div class="card">
            <h2><?= $id ? 'Edit Product' : 'New Product' ?></h2>
            <p style="color: #666; font-size: 14px;">
                Upload product images (JPG, PNG, WebP). Max 2MB.
            </p>
            <?= $crud->renderForm($id) ?>
        </div>

        <div class="card">
            <h2>Products</h2>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if ($product['image']): ?>
                                <img src="<?= htmlspecialchars($product['image']) ?>" class="product-img" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                <span style="color: #999;">No image</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td>$<?= number_format($product['price'], 2) ?></td>
                        <td><?= htmlspecialchars($product['category'] ?? 'N/A') ?></td>
                        <td><a href="?id=<?= $product['id'] ?>">Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p style="margin-top: 15px;"><a href="file-uploads.php">← Create new product</a></p>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>💡 Configure File Upload</h3>
        <p>Use metadata to control file upload behavior:</p>
        <pre style="background: white; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>ALTER TABLE products 
MODIFY COLUMN image VARCHAR(255) 
COMMENT '{
    "type": "file",
    "accept": "image/*",
    "max_size": 2097152,
    "label": "Product Image",
    "tooltip": "JPG, PNG or WebP. Max 2MB"
}';</code></pre>
    </div>

    <div style="margin-top: 20px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
        <h3>📚 Next Steps</h3>
        <ul>
            <li><a href="../04-advanced/hooks.php">Hooks System</a> - Add custom logic (e.g., image resizing)</li>
            <li><a href="../04-advanced/virtual-fields.php">Virtual Fields</a> - Password confirmation, terms</li>
            <li><a href="metadata.php">Back to Metadata</a></li>
        </ul>
    </div>
    <script src="../assets/dynamiccrud.js"></script>
</body>
</html>
