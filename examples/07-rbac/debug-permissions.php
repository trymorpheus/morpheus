<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$currentUserId = 3; // Author
$currentUserRole = 'author';

$crud = new DynamicCRUD($pdo, 'blog_posts');
$crud->setCurrentUser($currentUserId, $currentUserRole);

$pm = $crud->getPermissionManager();

echo "<h2>Debug Permissions for User #3 (Author)</h2>";
echo "<p>User ID: " . $pm->getCurrentUserId() . "</p>";
echo "<p>User Role: " . $pm->getCurrentUserRole() . "</p>";

// Get posts
$stmt = $pdo->query("SELECT * FROM blog_posts");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Post ID</th><th>Title</th><th>user_id</th><th>canUpdate()</th><th>canDelete()</th></tr>";

foreach ($posts as $post) {
    $canUpdate = $pm->canUpdate($post) ? '✅ YES' : '❌ NO';
    $canDelete = $pm->canDelete($post) ? '✅ YES' : '❌ NO';
    
    echo "<tr>";
    echo "<td>{$post['id']}</td>";
    echo "<td>{$post['title']}</td>";
    echo "<td>{$post['user_id']}</td>";
    echo "<td>{$canUpdate}</td>";
    echo "<td>{$canDelete}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Expected Results:</h3>";
echo "<ul>";
echo "<li>Post #1 (user_id=1): canUpdate=NO, canDelete=NO</li>";
echo "<li>Post #2 (user_id=2): canUpdate=NO, canDelete=NO</li>";
echo "<li>Post #3 (user_id=3): canUpdate=YES, canDelete=NO</li>";
echo "</ul>";
