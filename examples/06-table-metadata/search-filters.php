<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'posts');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search & Filters - DynamicCRUD</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 1200px;
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
        .list-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .list-header {
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .list-header h2 {
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .list-icon {
            font-size: 32px;
        }
        .list-description {
            color: #666;
            margin: 10px 0 0 0;
        }
        .list-search {
            margin-bottom: 20px;
        }
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .search-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .search-button, .clear-search {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .search-button {
            background: #667eea;
            color: white;
        }
        .search-button:hover {
            background: #5568d3;
        }
        .clear-search {
            background: #dc3545;
            color: white;
        }
        .clear-search:hover {
            background: #c82333;
        }
        .list-filters {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
        }
        .filters-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: #333;
        }
        .filter-group select, .filter-group input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .filter-button {
            padding: 8px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .filter-button:hover {
            background: #218838;
        }
        .list-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .list-table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .list-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        .list-table tr:hover {
            background: #f5f5f5;
        }
        .list-table a {
            color: #667eea;
            text-decoration: none;
            margin-right: 10px;
        }
        .list-table a:hover {
            text-decoration: underline;
        }
        .list-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 20px;
            padding: 20px;
        }
        .list-pagination a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .list-pagination a:hover {
            text-decoration: underline;
        }
        .list-pagination span {
            color: #666;
        }
    </style>
</head>
<body>
    <a href="../index.html" class="back-link">← Back to Examples</a>
    
    <div class="header">
        <h1>🔍 Search & Filters</h1>
        <p><strong>Table:</strong> posts</p>
        <p>Advanced search and filtering via table metadata</p>
    </div>

    <div class="info-box">
        <h3>✨ Features Demonstrated</h3>
        <ul>
            <li><strong>Full-text Search:</strong> Search across multiple fields (title, content)</li>
            <li><strong>Status Filter:</strong> Filter by draft/published status</li>
            <li><strong>Date Range Filter:</strong> Filter by creation date</li>
            <li><strong>Pagination:</strong> 3 records per page (configurable)</li>
            <li><strong>Zero Configuration:</strong> All defined in table metadata</li>
        </ul>
        
        <h3>📝 Table Metadata</h3>
        <pre style="background: #fff; padding: 10px; border-radius: 4px; overflow-x: auto;"><code>{
  "list_view": {
    "searchable": ["title", "content"],
    "per_page": 3
  },
  "filters": [
    {
      "field": "status",
      "type": "select",
      "label": "Estado",
      "options": ["draft", "published"]
    },
    {
      "field": "created_at",
      "type": "daterange",
      "label": "Fecha de Creación"
    }
  ]
}</code></pre>
    </div>

    <?php echo $crud->renderList(); ?>
</body>
</html>
