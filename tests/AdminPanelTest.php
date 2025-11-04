<?php

namespace DynamicCRUD\Tests;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\Admin\AdminPanel;
use PDO;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class AdminPanelTest extends TestCase
{
    private PDO $pdo;
    private AdminPanel $admin;

    protected function setUp(): void
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->admin = new AdminPanel($this->pdo);
    }

    public function testConstructor(): void
    {
        $admin = new AdminPanel($this->pdo);
        $this->assertInstanceOf(AdminPanel::class, $admin);
    }

    public function testConstructorWithConfig(): void
    {
        $admin = new AdminPanel($this->pdo, [
            'title' => 'Test Panel',
            'theme' => ['primary' => '#ff0000']
        ]);
        
        $this->assertInstanceOf(AdminPanel::class, $admin);
    }

    public function testAddTable(): void
    {
        $result = $this->admin->addTable('users');
        
        $this->assertInstanceOf(AdminPanel::class, $result);
    }

    public function testAddTableWithOptions(): void
    {
        $result = $this->admin->addTable('users', [
            'label' => 'Usuarios',
            'icon' => '👥',
            'hidden' => false
        ]);
        
        $this->assertInstanceOf(AdminPanel::class, $result);
    }

    public function testFluentInterface(): void
    {
        $result = $this->admin
            ->addTable('users', ['icon' => '👥'])
            ->addTable('posts', ['icon' => '📝']);
        
        $this->assertInstanceOf(AdminPanel::class, $result);
    }

    public function testRenderDashboard(): void
    {
        $this->admin->addTable('users', ['icon' => '👥', 'label' => 'Usuarios']);
        
        $_GET = ['action' => 'dashboard'];
        $output = $this->admin->render();
        
        $this->assertStringContainsString('Dashboard', $output);
        $this->assertStringContainsString('<!DOCTYPE html>', $output);
        $this->assertStringContainsString('sidebar', $output);
    }

    public function testRenderContainsSidebar(): void
    {
        $this->admin->addTable('users', ['icon' => '👥', 'label' => 'Usuarios']);
        
        $_GET = ['action' => 'dashboard'];
        $output = $this->admin->render();
        
        $this->assertStringContainsString('sidebar', $output);
        $this->assertStringContainsString('Usuarios', $output);
        $this->assertStringContainsString('👥', $output);
    }

    public function testRenderContainsHeader(): void
    {
        $_GET = ['action' => 'dashboard'];
        $output = $this->admin->render();
        
        $this->assertStringContainsString('header', $output);
        $this->assertStringContainsString('Admin', $output);
    }

    public function testRenderContainsBreadcrumbs(): void
    {
        $_GET = ['action' => 'dashboard'];
        $output = $this->admin->render();
        
        $this->assertStringContainsString('breadcrumbs', $output);
        $this->assertStringContainsString('Inicio', $output);
    }

    public function testCustomTitle(): void
    {
        $admin = new AdminPanel($this->pdo, ['title' => 'Custom Admin']);
        
        $_GET = ['action' => 'dashboard'];
        $output = $admin->render();
        
        $this->assertStringContainsString('Custom Admin', $output);
    }

    public function testCustomTheme(): void
    {
        $admin = new AdminPanel($this->pdo, [
            'theme' => ['primary' => '#ff0000']
        ]);
        
        $_GET = ['action' => 'dashboard'];
        $output = $admin->render();
        
        $this->assertStringContainsString('#ff0000', $output);
    }

    public function testDashboardShowsStats(): void
    {
        $this->admin->addTable('users', ['icon' => '👥', 'label' => 'Usuarios']);
        
        $_GET = ['action' => 'dashboard'];
        $output = $this->admin->render();
        
        $this->assertStringContainsString('stat-card', $output);
        $this->assertStringContainsString('Usuarios', $output);
    }
}
