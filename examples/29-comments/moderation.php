<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\Comments\CommentManager;

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize comment manager
$commentManager = new CommentManager($pdo);

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    
    switch ($_GET['action']) {
        case 'approve':
            $commentManager->approve($id);
            break;
        case 'reject':
            $commentManager->reject($id);
            break;
        case 'delete':
            $commentManager->delete($id);
            break;
    }
    
    header('Location: moderation.php');
    exit;
}

// Get pending comments
$pending = $commentManager->getPending();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment Moderation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
        }
        .comment-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .comment-author {
            font-weight: bold;
        }
        .comment-date {
            color: #999;
            font-size: 14px;
        }
        .comment-post {
            color: #667eea;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .comment-content {
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-approve {
            background: #28a745;
            color: white;
        }
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        .btn-delete {
            background: #6c757d;
            color: white;
        }
        .no-comments {
            text-align: center;
            color: #999;
            padding: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>💬 Comment Moderation</h1>
        
        <?php if (empty($pending)): ?>
            <div class="no-comments">
                <p>✅ No pending comments!</p>
            </div>
        <?php else: ?>
            <?php foreach ($pending as $comment): ?>
                <div class="comment-card">
                    <div class="comment-header">
                        <div>
                            <span class="comment-author"><?= htmlspecialchars($comment['author_name']) ?></span>
                            <span style="color: #999;"> (<?= htmlspecialchars($comment['author_email']) ?>)</span>
                        </div>
                        <div class="comment-date">
                            <?= date('M j, Y g:i A', strtotime($comment['created_at'])) ?>
                        </div>
                    </div>
                    
                    <div class="comment-post">
                        On post: <?= htmlspecialchars($comment['post_title'] ?? 'Unknown') ?>
                    </div>
                    
                    <div class="comment-content">
                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                    </div>
                    
                    <div class="actions">
                        <a href="?action=approve&id=<?= $comment['id'] ?>" class="btn btn-approve">
                            ✅ Approve
                        </a>
                        <a href="?action=reject&id=<?= $comment['id'] ?>" class="btn btn-reject">
                            ❌ Reject
                        </a>
                        <a href="?action=delete&id=<?= $comment['id'] ?>" class="btn btn-delete" 
                           onclick="return confirm('Delete this comment permanently?')">
                            🗑️ Delete
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
