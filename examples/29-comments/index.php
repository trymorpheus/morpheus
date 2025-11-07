<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\Comments\CommentManager;
use DynamicCRUD\Comments\CommentRenderer;

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize comment manager
$commentManager = new CommentManager($pdo, 'comments', $requireApproval = false);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'add_comment') {
    $result = $commentManager->add(
        (int) $_POST['post_id'],
        $_POST['author_name'],
        $_POST['author_email'],
        $_POST['content'],
        !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null
    );

    if ($result['success']) {
        header('Location: index.php?post_id=' . $_POST['post_id'] . '&success=1');
        exit;
    } else {
        $error = $result['error'];
    }
}

// Get post
$postId = (int) ($_GET['post_id'] ?? 1);
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
$stmt->execute(['id' => $postId]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die('Post not found');
}

// Render comments
$renderer = new CommentRenderer($commentManager, $allowReplies = true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - Comments Demo</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .post-meta {
            color: #999;
            margin-bottom: 20px;
        }
        .post-content {
            line-height: 1.8;
            margin-bottom: 40px;
            padding-bottom: 40px;
            border-bottom: 2px solid #eee;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <div class="post-meta">
            Posted on <?= date('F j, Y', strtotime($post['created_at'])) ?>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                ✅ Comment posted successfully!
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="post-content">
            <?= nl2br(htmlspecialchars($post['content'])) ?>
        </div>

        <?= $renderer->render($postId) ?>
    </div>
</body>
</html>
