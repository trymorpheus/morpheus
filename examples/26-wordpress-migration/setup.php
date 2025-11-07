<?php

require __DIR__ . '/../../vendor/autoload.php';

echo "🔧 WordPress Migration - Database Setup\n";
echo "=======================================\n\n";

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$prefix = 'wp_';

echo "📦 Creating tables with prefix '{$prefix}'...\n\n";

// Drop existing tables
echo "  🗑️  Dropping existing tables...\n";
$pdo->exec("DROP TABLE IF EXISTS {$prefix}post_tags");
$pdo->exec("DROP TABLE IF EXISTS {$prefix}comments");
$pdo->exec("DROP TABLE IF EXISTS {$prefix}posts");
$pdo->exec("DROP TABLE IF EXISTS {$prefix}categories");
$pdo->exec("DROP TABLE IF EXISTS {$prefix}tags");

// Create categories table
echo "  📁 Creating {$prefix}categories...\n";
$pdo->exec("
    CREATE TABLE {$prefix}categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Create tags table
echo "  🏷️  Creating {$prefix}tags...\n";
$pdo->exec("
    CREATE TABLE {$prefix}tags (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Create posts table
echo "  📝 Creating {$prefix}posts...\n";
$pdo->exec("
    CREATE TABLE {$prefix}posts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        content TEXT,
        excerpt TEXT,
        status ENUM('draft', 'published') DEFAULT 'draft',
        published_at DATETIME,
        category_id INT,
        featured_image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at TIMESTAMP NULL,
        FOREIGN KEY (category_id) REFERENCES {$prefix}categories(id)
    )
");

// Create post_tags pivot table
echo "  🔗 Creating {$prefix}post_tags...\n";
$pdo->exec("
    CREATE TABLE {$prefix}post_tags (
        post_id INT NOT NULL,
        tag_id INT NOT NULL,
        PRIMARY KEY (post_id, tag_id),
        FOREIGN KEY (post_id) REFERENCES {$prefix}posts(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES {$prefix}tags(id) ON DELETE CASCADE
    )
");

echo "\n✅ Database setup complete!\n\n";
echo "📊 Tables created:\n";
echo "  - {$prefix}categories\n";
echo "  - {$prefix}tags\n";
echo "  - {$prefix}posts\n";
echo "  - {$prefix}post_tags\n\n";

echo "🚀 Next step: Run the migration\n";
echo "   php migrate.php\n";
