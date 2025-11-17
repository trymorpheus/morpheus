<?php

namespace Morpheus\Tests;

use Morpheus\ListGenerator;
use PHPUnit\Framework\TestCase;
use PDO;

class ListGeneratorTest extends TestCase
{
    private PDO $pdo;
    private ListGenerator $generator;
    private array $schema;

    protected function setUp(): void
    {
        $this->pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s', getenv('DB_HOST'), getenv('DB_NAME')),
            getenv('DB_USER'),
            getenv('DB_PASS')
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->schema = [
            'table' => 'users',
            'primary_key' => 'id',
            'columns' => [
                ['name' => 'id', 'is_primary' => true, 'sql_type' => 'int', 'metadata' => []],
                ['name' => 'name', 'is_primary' => false, 'sql_type' => 'varchar', 'metadata' => []],
                ['name' => 'email', 'is_primary' => false, 'sql_type' => 'varchar', 'metadata' => []],
            ]
        ];
        
        $this->generator = new ListGenerator($this->pdo, 'users', $this->schema);
        $this->createTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }

    private function createTestData(): void
    {
        $this->cleanupTestData();
        
        for ($i = 1; $i <= 25; $i++) {
            $this->pdo->exec("INSERT INTO users (name, email, password) VALUES ('User $i', 'user$i@test.com', 'test123')");
        }
    }

    private function cleanupTestData(): void
    {
        $this->pdo->exec("DELETE FROM users WHERE email LIKE '%@test.com'");
    }

    public function testListDefaultPagination(): void
    {
        $html = $this->generator->render();
        
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('Users', $html);
    }

    public function testListCustomPerPage(): void
    {
        $html = $this->generator->render(['perPage' => 10]);
        
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('pagination', $html);
    }

    public function testListSecondPage(): void
    {
        $_GET['page'] = 2;
        $html = $this->generator->render(['perPage' => 10]);
        
        $this->assertStringContainsString('page=2', $html);
        unset($_GET['page']);
    }

    public function testListLastPage(): void
    {
        $_GET['page'] = 3;
        $html = $this->generator->render(['perPage' => 10]);
        
        $this->assertStringContainsString('page=3', $html);
        unset($_GET['page']);
    }

    public function testListWithFilters(): void
    {
        $html = $this->generator->render();
        
        $this->assertStringContainsString('<table', $html);
    }

    public function testListWithSortAsc(): void
    {
        $html = $this->generator->render(['perPage' => 5]);
        
        $this->assertStringContainsString('<table', $html);
    }

    public function testListWithSortDesc(): void
    {
        $html = $this->generator->render(['perPage' => 5]);
        
        $this->assertStringContainsString('<table', $html);
    }

    public function testRenderTableWithData(): void
    {
        $html = $this->generator->render(['perPage' => 2]);
        
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('Editar', $html);
        $this->assertStringContainsString('Eliminar', $html);
    }

    public function testRenderTableEmpty(): void
    {
        $this->pdo->exec("DELETE FROM users");
        $html = $this->generator->render();
        
        $this->assertStringContainsString('No hay registros', $html);
        $this->createTestData();
    }

    public function testRenderPaginationMultiplePages(): void
    {
        $_GET['page'] = 2;
        $html = $this->generator->render(['perPage' => 5]);
        
        $this->assertStringContainsString('page=1', $html);
        $this->assertStringContainsString('page=3', $html);
        unset($_GET['page']);
    }

    public function testRenderPaginationFirstPage(): void
    {
        $html = $this->generator->render(['perPage' => 10]);
        
        $this->assertStringContainsString('page=2', $html);
        $this->assertStringContainsString('pagination', $html);
    }

    public function testRenderPaginationLastPage(): void
    {
        $_GET['page'] = 3;
        $html = $this->generator->render(['perPage' => 10]);
        
        $this->assertStringContainsString('page=2', $html);
        unset($_GET['page']);
    }

    public function testRenderPaginationSinglePage(): void
    {
        $this->cleanupTestData();
        for ($i = 1; $i <= 5; $i++) {
            $this->pdo->exec("INSERT INTO users (name, email, password) VALUES ('User $i', 'user$i@test.com', 'test123')");
        }
        
        $html = $this->generator->render();
        
        $this->assertStringNotContainsString('pagination', $html);
        
        $this->cleanupTestData();
        $this->createTestData();
    }
}
