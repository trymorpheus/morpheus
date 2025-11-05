<?php
/**
 * Public Blog - Frontend
 * 
 * This is the public-facing blog powered by DynamicCRUD Universal CMS
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\Frontend\FrontendRouter;
use DynamicCRUD\Frontend\FrontendRenderer;
use DynamicCRUD\Frontend\SEOManager;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize components
$router = new FrontendRouter();
$seo = new SEOManager($pdo, 'http://localhost/examples/24-blog-cms', 'My Blog', '24_');
$renderer = new FrontendRenderer($pdo, 'blog', null, $seo, '24_');

// Get request URI
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = parse_url($uri, PHP_URL_PATH);
$uri = str_replace('/examples/24-blog-cms', '', $uri);

// Route the request
$route = $router->match($uri);

if (!$route) {
    http_response_code(404);
    echo $renderer->render404();
    exit;
}

// Handle the route
$html = '';

switch ($route->handler) {
    case 'home':
        $html = $renderer->renderHome();
        break;
        
    case 'blog.archive':
        $page = $route->params['page'] ?? 1;
        $html = $renderer->renderArchive((int)$page);
        break;
        
    case 'blog.single':
        $html = $renderer->renderSingle($route->params['slug']);
        break;
        
    case 'blog.category':
        $html = $renderer->renderCategory($route->params['slug']);
        break;
        
    case 'blog.tag':
        $html = $renderer->renderTag($route->params['slug']);
        break;
        
    case 'search':
        $query = $_GET['q'] ?? '';
        $html = $renderer->renderSearch($query);
        break;
        
    default:
        http_response_code(404);
        $html = $renderer->render404();
}

echo $html;
