<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'posts');

// Add M:N relationship for tags
$crud->addManyToMany('tags', 'post_tags', 'post_id', 'tag_id', 'tags');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        echo "<div style='padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin: 20px;'>";
        echo "✅ Post saved successfully! ID: {$result['id']}<br>";
        echo "🔗 Slug was auto-generated from title<br>";
        echo "⏰ Timestamps were automatically set<br>";
        echo "<a href='?id={$result['id']}' style='color: #155724;'>Edit this post</a> | ";
        echo "<a href='?' style='color: #155724;'>Create new post</a>";
        echo "</div>";
    } else {
        echo "<div style='padding: 15px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px;'>";
        echo "❌ Error: " . ($result['error'] ?? 'Validation failed');
        if (isset($result['errors'])) {
            echo "<ul>";
            foreach ($result['errors'] as $field => $error) {
                echo "<li><strong>{$field}:</strong> {$error}</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automatic Behaviors - DynamicCRUD</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            opacity: 0.9;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #1976D2;
        }
        .info-box ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .info-box code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #d63384;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <a href="../index.html" class="back-link">← Back to Examples</a>
    
    <div class="header">
        <h1>🤖 Automatic Behaviors</h1>
        <p><strong>Table:</strong> posts</p>
        <p>Demonstrates auto-timestamps and auto-slug generation via table metadata</p>
    </div>

    <div class="info-box">
        <h3>✨ Automatic Behaviors Enabled</h3>
        <ul>
            <li><strong>Auto-Timestamps:</strong> <code>created_at</code> and <code>updated_at</code> are automatically set</li>
            <li><strong>Auto-Slug:</strong> <code>slug</code> is generated from <code>title</code> (lowercase, hyphenated, unique)</li>
            <li><strong>Zero Configuration:</strong> Behaviors defined in table metadata, no code needed</li>
        </ul>
        
        <h3>📝 How It Works</h3>
        <p>Table metadata in <code>posts</code> table comment:</p>
        <pre style="background: #fff; padding: 10px; border-radius: 4px; overflow-x: auto;"><code>{
  "behaviors": {
    "timestamps": {
      "created_at": "created_at",
      "updated_at": "updated_at"
    },
    "sluggable": {
      "source": "title",
      "target": "slug",
      "unique": true,
      "separator": "-",
      "lowercase": true
    }
  }
}</code></pre>
        
        <h3>🎯 Try It</h3>
        <ul>
            <li>Enter a title like "My Amazing Blog Post"</li>
            <li>Leave the slug field empty (it's readonly)</li>
            <li>Submit the form</li>
            <li>The slug will be auto-generated as "my-amazing-blog-post"</li>
            <li>Timestamps will be automatically set</li>
            <li>If you create another post with the same title, slug will be "my-amazing-blog-post-1"</li>
        </ul>
    </div>

    <?php echo $crud->renderForm($_GET['id'] ?? null); ?>

    <script src="../dynamiccrud.js"></script>
</body>
</html>
