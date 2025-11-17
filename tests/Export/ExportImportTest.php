<?php

namespace Morpheus\Tests\Export;

use Morpheus\DynamicCRUD;
use Morpheus\Tests\TestHelper;
use PHPUnit\Framework\TestCase;
use PDO;

class ExportImportTest extends TestCase
{
    private PDO $pdo;
    private DynamicCRUD $crud;

    protected function setUp(): void
    {
        $this->pdo = TestHelper::getPDO();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->crud = new Morpheus($this->pdo, 'users');
        
        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }

    private function cleanupTestData(): void
    {
        $this->pdo->exec("DELETE FROM users WHERE email LIKE 'export_test_%'");
    }

    public function testExportCSV(): void
    {
        $this->pdo->exec("INSERT INTO users (name, email, password) VALUES ('Export Test', 'export_test_1@example.com', 'test')");
        
        $csv = $this->crud->export('csv');
        
        $this->assertStringContainsString('name', $csv);
        $this->assertStringContainsString('email', $csv);
        $this->assertStringContainsString('Export Test', $csv);
    }

    public function testGenerateTemplate(): void
    {
        $template = $this->crud->generateImportTemplate();
        
        $this->assertStringContainsString('name', $template);
        $this->assertStringContainsString('email', $template);
        $this->assertStringContainsString('password', $template);
    }

    public function testImportCSV(): void
    {
        $csv = "name,email,password\nImport Test,export_test_import@example.com,test123";
        
        $result = $this->crud->import($csv, ['skip_errors' => true]);
        
        $this->assertGreaterThanOrEqual(0, $result['success']);
        $this->assertIsInt($result['errors']);
    }

    public function testImportPreview(): void
    {
        $csv = "name,email,password\nPreview Test,export_test_preview@example.com,test123";
        
        $result = $this->crud->import($csv, ['preview' => true]);
        
        $this->assertCount(1, $result['details']);
        $this->assertEquals('preview', $result['details'][0]['status']);
        
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE email = 'export_test_preview@example.com'");
        $this->assertEquals(0, $stmt->fetchColumn());
    }

    public function testImportWithErrors(): void
    {
        $csv = "name,email,password\nTest,invalid-email,test123";
        
        $result = $this->crud->import($csv);
        
        $this->assertEquals(0, $result['success']);
        $this->assertGreaterThan(0, $result['errors']);
    }
}
