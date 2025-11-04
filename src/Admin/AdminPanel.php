<?php

namespace DynamicCRUD\Admin;

use PDO;
use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\ListGenerator;
use DynamicCRUD\SchemaAnalyzer;
use DynamicCRUD\Metadata\TableMetadata;
use DynamicCRUD\GlobalMetadata;
use DynamicCRUD\UI\Components;

class AdminPanel
{
    private PDO $pdo;
    private array $tables = [];
    private array $config = [];
    private ?GlobalMetadata $globalConfig = null;

    public function __construct(PDO $pdo, array $config = [])
    {
        $this->pdo = $pdo;
        $defaults = [
            'title' => 'Admin Panel',
            'logo' => null,
            'theme' => [
                'primary' => '#667eea',
                'sidebar_bg' => '#2d3748',
                'sidebar_text' => '#e2e8f0'
            ]
        ];
        
        $this->config = array_merge($defaults, $config);
        if (isset($config['theme'])) {
            $this->config['theme'] = array_merge($defaults['theme'], $config['theme']);
        }
        
        $this->globalConfig = new GlobalMetadata($pdo);
    }

    public function addTable(string $table, array $options = []): self
    {
        $this->tables[$table] = array_merge([
            'label' => ucfirst($table),
            'icon' => '📋',
            'hidden' => false
        ], $options);
        
        return $this;
    }

    public function render(): string
    {
        $action = $_GET['action'] ?? 'dashboard';
        $table = $_GET['table'] ?? null;
        $id = $_GET['id'] ?? null;

        if ($action === 'dashboard') {
            return $this->renderLayout($this->renderDashboard());
        }

        if ($action === 'list' && $table) {
            return $this->renderLayout($this->renderList($table));
        }

        if ($action === 'form' && $table) {
            return $this->renderLayout($this->renderForm($table, $id));
        }

        if ($action === 'delete' && $table && $id) {
            return $this->handleDelete($table, $id);
        }

        return $this->renderLayout('<h1>404 - Not Found</h1>');
    }

    private function renderLayout(string $content): string
    {
        $sidebar = $this->renderSidebar();
        $header = $this->renderHeader();
        $breadcrumbs = $this->renderBreadcrumbs();
        
        $primary = $this->config['theme']['primary'];
        $sidebarBg = $this->config['theme']['sidebar_bg'];
        $sidebarText = $this->config['theme']['sidebar_text'];

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$this->config['title']}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f7fafc; }
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: {$sidebarBg}; color: {$sidebarText}; position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h1 { font-size: 20px; font-weight: 600; }
        .sidebar-nav { padding: 10px 0; }
        .sidebar-nav a { display: flex; align-items: center; padding: 12px 20px; color: {$sidebarText}; text-decoration: none; transition: background 0.2s; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(255,255,255,0.1); }
        .sidebar-nav a .icon { margin-right: 10px; font-size: 18px; }
        .main-content { margin-left: 250px; flex: 1; }
        .header { background: white; padding: 15px 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .breadcrumbs { padding: 15px 30px; color: #718096; font-size: 14px; }
        .breadcrumbs a { color: {$primary}; text-decoration: none; }
        .content { padding: 30px; }
        .card { background: white; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h2 { margin-bottom: 20px; color: #2d3748; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid {$primary}; }
        .stat-card .label { color: #718096; font-size: 14px; margin-bottom: 5px; }
        .stat-card .value { font-size: 32px; font-weight: bold; color: #2d3748; }
        .stat-card .icon { font-size: 24px; float: right; }
        .btn { display: inline-block; padding: 10px 20px; background: {$primary}; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; border: none; cursor: pointer; }
        .btn:hover { opacity: 0.9; }
        .btn-secondary { background: #718096; }
        .btn-danger { background: #e53e3e; }
        .list-table { width: 100%; border-collapse: collapse; }
        .list-table th, .list-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .list-table th { background: #f7fafc; font-weight: 600; color: #2d3748; }
        .list-table tr:hover { background: #f7fafc; }
        .action-edit, .action-delete { display: inline-block; padding: 6px 12px; margin-right: 5px; border-radius: 4px; text-decoration: none; font-size: 13px; }
        .action-edit { background: {$primary}; color: white; }
        .action-edit:hover { opacity: 0.8; }
        .action-delete { background: #e53e3e; color: white; }
        .action-delete:hover { opacity: 0.8; }
        form { max-width: 100%; }
        form label { display: block; margin-bottom: 5px; font-weight: 500; color: #2d3748; }
        form input, form select, form textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 14px; }
        form input:focus, form select:focus, form textarea:focus { outline: none; border-color: {$primary}; }
        form button[type="submit"] { background: {$primary}; color: white; padding: 12px 24px; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; }
        form button[type="submit"]:hover { opacity: 0.9; }
        .user-menu { display: flex; align-items: center; gap: 15px; }
        .user-avatar { width: 35px; height: 35px; border-radius: 50%; background: {$primary}; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar-nav a span { display: none; }
            .main-content { margin-left: 70px; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        {$sidebar}
        <div class="main-content">
            {$header}
            {$breadcrumbs}
            <div class="content">
                {$content}
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function renderSidebar(): string
    {
        $logo = $this->config['logo'] ?? $this->config['title'];
        $nav = '<div class="sidebar-nav">';
        
        $nav .= sprintf(
            '<a href="?action=dashboard" class="%s"><span class="icon">📊</span><span>Dashboard</span></a>',
            ($_GET['action'] ?? 'dashboard') === 'dashboard' ? 'active' : ''
        );

        foreach ($this->tables as $table => $options) {
            if ($options['hidden']) continue;
            
            $active = ($_GET['table'] ?? '') === $table ? 'active' : '';
            $nav .= sprintf(
                '<a href="?action=list&table=%s" class="%s"><span class="icon">%s</span><span>%s</span></a>',
                $table,
                $active,
                $options['icon'],
                $options['label']
            );
        }

        $nav .= '</div>';

        return <<<HTML
<div class="sidebar">
    <div class="sidebar-header">
        <h1>{$logo}</h1>
    </div>
    {$nav}
</div>
HTML;
    }

    private function renderHeader(): string
    {
        $user = 'Admin';
        $avatar = strtoupper(substr($user, 0, 1));

        return <<<HTML
<div class="header">
    <div></div>
    <div class="user-menu">
        <span>{$user}</span>
        <div class="user-avatar">{$avatar}</div>
    </div>
</div>
HTML;
    }

    private function renderBreadcrumbs(): string
    {
        $action = $_GET['action'] ?? 'dashboard';
        $table = $_GET['table'] ?? null;
        
        $crumbs = ['<a href="?action=dashboard">🏠 Inicio</a>'];

        if ($action === 'list' && $table) {
            $label = $this->tables[$table]['label'] ?? ucfirst($table);
            $crumbs[] = $label;
        }

        if ($action === 'form' && $table) {
            $label = $this->tables[$table]['label'] ?? ucfirst($table);
            $crumbs[] = sprintf('<a href="?action=list&table=%s">%s</a>', $table, $label);
            $crumbs[] = isset($_GET['id']) ? 'Editar' : 'Nuevo';
        }

        return '<div class="breadcrumbs">' . implode(' / ', $crumbs) . '</div>';
    }

    private function renderDashboard(): string
    {
        // Set theme for Components
        Components::setTheme(['primary' => $this->config['theme']['primary']]);
        
        $stats = '';
        
        foreach ($this->tables as $table => $options) {
            if ($options['hidden']) continue;
            
            $count = $this->pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            
            $stats .= '<div style="margin-bottom: 20px;">' . 
                      Components::statCard($options['label'], number_format($count)) . 
                      '</div>';
        }

        $welcomeCard = Components::card(
            'Bienvenido al Panel de Administración',
            '<p>Selecciona una opción del menú lateral para comenzar.</p>'
        );

        return <<<HTML
<h1 style="margin-bottom: 30px;">Dashboard</h1>
<div class="stats-grid">
    {$stats}
</div>
{$welcomeCard}
HTML;
    }

    private function renderList(string $table): string
    {
        $label = $this->tables[$table]['label'] ?? ucfirst($table);
        
        if (isset($_GET['delete'])) {
            $crud = new DynamicCRUD($this->pdo, $table);
            $crud->delete((int)$_GET['delete']);
            header("Location: ?action=list&table={$table}&deleted=1");
            exit;
        }
        
        $analyzer = new SchemaAnalyzer($this->pdo);
        $schema = $analyzer->getTableSchema($table);
        $tableMetadata = new TableMetadata($this->pdo, $table);
        
        $listGen = new ListGenerator($this->pdo, $table, $schema, $tableMetadata);
        $list = $listGen->render();
        
        $list = preg_replace('/href="\?[^"]*id=(\d+)"/', 'href="?action=form&amp;table=' . $table . '&amp;id=$1"', $list);
        $list = preg_replace('/href="\?[^"]*delete=(\d+)"/', 'href="?action=list&amp;table=' . $table . '&amp;delete=$1"', $list);

        $newBtn = Components::button('➕ Nuevo', 'primary', ['href' => "?action=form&table={$table}"]);
        
        $successMsg = '';
        if (isset($_GET['success'])) {
            $successMsg = Components::alert('✅ Guardado correctamente', 'success', false);
        }
        if (isset($_GET['deleted'])) {
            $successMsg = Components::alert('✅ Eliminado correctamente', 'success', false);
        }

        return <<<HTML
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>{$label}</h1>
    {$newBtn}
</div>
{$successMsg}
<div class="card">
    {$list}
</div>
HTML;
    }

    private function renderForm(string $table, ?string $id): string
    {
        $label = $this->tables[$table]['label'] ?? ucfirst($table);
        $title = $id ? "Editar {$label}" : "Nuevo {$label}";

        $crud = new DynamicCRUD($this->pdo, $table);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $crud->handleSubmission();
            
            if ($result['success']) {
                header("Location: ?action=list&table={$table}&success=1");
                exit;
            }
            
            $error = $result['error'] ?? 'Error al guardar';
            $errorMsg = Components::alert($error, 'danger', false);
        }

        $form = $crud->renderForm($id);
        $backBtn = sprintf('<a href="?action=list&table=%s" class="btn btn-secondary">← Volver</a>', $table);
        $errorDisplay = $errorMsg ?? '';

        return <<<HTML
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>{$title}</h1>
    {$backBtn}
</div>
{$errorDisplay}
<div class="card">
    {$form}
</div>
HTML;
    }

    private function handleDelete(string $table, string $id): void
    {
        $crud = new DynamicCRUD($this->pdo, $table);
        $crud->delete((int)$id);
        
        header("Location: ?action=list&table={$table}&deleted=1");
        exit;
    }
}
