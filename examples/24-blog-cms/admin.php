<?php
/**
 * Admin Panel - Backend
 * 
 * Manage your blog content (posts, categories, tags)
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;
use Morpheus\Admin\AdminPanel;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create admin panel
$admin = new AdminPanel($pdo, [
    'title' => 'Blog CMS Admin',
    'theme' => [
        'primary' => '#667eea',
        'sidebar_bg' => '#2d3748',
        'sidebar_text' => '#e2e8f0'
    ]
]);

// Add tables with prefix
$admin->addTable('24_posts', [
    'icon' => '📝',
    'label' => 'Posts',
    'description' => 'Manage blog posts'
]);

$admin->addTable('24_categories', [
    'icon' => '📁',
    'label' => 'Categories',
    'description' => 'Organize posts by category'
]);

$admin->addTable('24_tags', [
    'icon' => '🏷️',
    'label' => 'Tags',
    'description' => 'Tag your posts'
]);

$admin->addTable('24_comments', [
    'icon' => '💬',
    'label' => 'Comments',
    'description' => 'Manage comments'
]);

// Handle table actions
$table = $_GET['table'] ?? null;
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($table && in_array($table, ['24_posts', '24_categories', '24_tags', '24_comments'])) {
    $crud = new Morpheus($pdo, $table);
    
    // Configure many-to-many for posts
    if ($table === '24_posts') {
        $crud->addManyToMany('tags', '24_post_tags', 'post_id', 'tag_id', '24_tags');
    }
    
    if ($action === 'form') {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html><head><meta charset="UTF-8"><title><?= ucfirst($table) ?></title>
        <style>
            body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; }
            .admin-layout { display: flex; min-height: 100vh; }
            .sidebar { width: 250px; background: #2d3748; color: #e2e8f0; padding: 20px; }
            .sidebar h2 { margin: 0 0 30px 0; color: white; }
            .sidebar a { display: block; padding: 10px; color: #e2e8f0; text-decoration: none; border-radius: 4px; margin-bottom: 5px; }
            .sidebar a:hover { background: #4a5568; }
            .main-content { flex: 1; padding: 30px; }
            .back-link { display: inline-block; margin-bottom: 20px; color: #667eea; text-decoration: none; }
            .alert-success { color: #155724; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px; }
            .alert-error { color: #721c24; padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 20px; }
        </style>
        </head><body>
        <div class="admin-layout">
            <div class="sidebar">
                <h2>Blog CMS Admin</h2>
                <a href="admin.php">Dashboard</a>
                <a href="admin.php?action=list&table=24_posts">📝 Posts</a>
                <a href="admin.php?action=list&table=24_categories">📁 Categories</a>
                <a href="admin.php?action=list&table=24_tags">🏷️ Tags</a>
                <a href="admin.php?action=list&table=24_comments">💬 Comments</a>
            </div>
            <div class="main-content">
                <a href="admin.php?action=list&table=<?= $table ?>" class="back-link">← Back to list</a>
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = $crud->handleSubmission();
                    if ($result['success']) {
                        echo '<div class="alert-success">✅ Saved successfully! <a href="admin.php?action=list&table=' . $table . '">View all</a></div>';
                    } else {
                        echo '<div class="alert-error">❌ Error: ' . ($result['error'] ?? 'Unknown error') . '</div>';
                    }
                }
                echo $crud->renderForm($id);
                ?>
            </div>
        </div>
        </body></html>
        <?php
        echo ob_get_clean();
        exit;
    } elseif ($action === 'delete' && $id) {
        $crud->delete((int)$id);
        header('Location: admin.php?action=list&table=' . $table);
        exit;
    } elseif ($action === 'list') {
        $pk = $crud->getSchema()['primary_key'];
        
        // Build query with JOINs for foreign keys
        $schema = $crud->getSchema();
        $sql = "SELECT {$table}.*";
        $joins = [];
        $joinCounter = 0;
        
        foreach ($schema['foreign_keys'] as $fkColumn => $fkInfo) {
            $relatedTable = $fkInfo['table'];
            $relatedColumn = $fkInfo['column'];
            $alias = "fk_{$joinCounter}";
            $joinCounter++;
            
            // Try to find display column
            $displayCol = null;
            $possibleColumns = ['name', 'title', 'author_name', 'slug'];
            
            foreach ($possibleColumns as $col) {
                try {
                    $checkStmt = $pdo->query("SHOW COLUMNS FROM {$relatedTable} LIKE '{$col}'");
                    if ($checkStmt->rowCount() > 0) {
                        $displayCol = $col;
                        break;
                    }
                } catch (\PDOException $e) {}
            }
            
            // If no display column found, use the related column (usually 'id')
            if (!$displayCol) {
                $displayCol = $relatedColumn;
            }
            
            // Only add display column if it's different from the FK column
            if ($displayCol !== $relatedColumn) {
                $sql .= ", {$alias}.{$displayCol} as {$fkColumn}_display";
            }
            $joins[] = "LEFT JOIN {$relatedTable} {$alias} ON {$table}.{$fkColumn} = {$alias}.{$relatedColumn}";
        }
        
        $sql .= " FROM {$table} " . implode(' ', $joins);
        
        $stmt = $pdo->query($sql);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html><head><meta charset="UTF-8"><title><?= ucfirst($table) ?></title>
        <style>
            body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; }
            .admin-layout { display: flex; min-height: 100vh; }
            .sidebar { width: 250px; background: #2d3748; color: #e2e8f0; padding: 20px; }
            .sidebar h2 { margin: 0 0 30px 0; color: white; }
            .sidebar a { display: block; padding: 10px; color: #e2e8f0; text-decoration: none; border-radius: 4px; margin-bottom: 5px; }
            .sidebar a:hover { background: #4a5568; }
            .main-content { flex: 1; padding: 30px; }
            .btn-create { display: inline-block; margin-bottom: 20px; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; }
            table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
            thead tr { background: #f8f9fa; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
            th { border-bottom: 2px solid #dee2e6; }
            a.action { color: #667eea; text-decoration: none; margin-right: 10px; }
            a.delete { color: #e53e3e; }
        </style>
        </head><body>
        <div class="admin-layout">
            <div class="sidebar">
                <h2>Blog CMS Admin</h2>
                <a href="admin.php">Dashboard</a>
                <a href="admin.php?action=list&table=24_posts">📝 Posts</a>
                <a href="admin.php?action=list&table=24_categories">📁 Categories</a>
                <a href="admin.php?action=list&table=24_tags">🏷️ Tags</a>
                <a href="admin.php?action=list&table=24_comments">💬 Comments</a>
            </div>
            <div class="main-content">
                <h1><?= ucfirst($table) ?></h1>
                <a href="admin.php?action=form&table=<?= $table ?>" class="btn-create">+ Create New</a>
                <?php if (empty($records)): ?>
                    <p>No records found.</p>
                <?php else: ?>
                    <table>
                        <thead><tr>
                            <?php foreach (array_keys($records[0]) as $column): ?>
                                <?php if (strpos($column, '_display') === false): ?>
                                    <th><?= htmlspecialchars($column) ?></th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <th>Actions</th>
                        </tr></thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <?php foreach ($record as $key => $value): ?>
                                        <?php
                                        // Skip _display columns in output, they're used to replace FK values
                                        if (strpos($key, '_display') !== false) continue;
                                        
                                        // If this is a FK column and we have a display value, use it
                                        $displayKey = $key . '_display';
                                        if (isset($record[$displayKey]) && $record[$displayKey]) {
                                            $value = $record[$displayKey];
                                        }
                                        ?>
                                        <td><?= htmlspecialchars(substr($value ?? '', 0, 50)) ?></td>
                                    <?php endforeach; ?>
                                    <td>
                                        <a href="admin.php?action=form&table=<?= $table ?>&id=<?= $record[$pk] ?>" class="action">Edit</a>
                                        <a href="admin.php?action=delete&table=<?= $table ?>&id=<?= $record[$pk] ?>" class="action delete" onclick="return confirm('Delete?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        </body></html>
        <?php
        echo ob_get_clean();
        exit;
    }
}

// Render admin panel
echo $admin->render();
