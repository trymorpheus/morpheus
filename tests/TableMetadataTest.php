<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\Metadata\TableMetadata;
use PDO;

class TableMetadataTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = TestHelper::getPDO();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create test table with metadata
        $this->pdo->exec("DROP TABLE IF EXISTS test_metadata");
        $this->pdo->exec("
            CREATE TABLE test_metadata (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(255)
            ) COMMENT '{}'
        ");
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS test_metadata");
    }

    private function setTableComment(array $metadata): void
    {
        $json = json_encode($metadata);
        $this->pdo->exec("ALTER TABLE test_metadata COMMENT = '" . addslashes($json) . "'");
    }

    public function testGetPermissions(): void
    {
        $metadata = [
            'permissions' => [
                'create' => ['admin'],
                'update' => ['admin', 'editor']
            ]
        ];
        
        $this->setTableComment($metadata);
        $tm = new TableMetadata($this->pdo, 'test_metadata');

        $this->assertTrue($tm->hasPermissions());
        $this->assertEquals($metadata['permissions'], $tm->getPermissions());
    }

    public function testGetRowLevelSecurity(): void
    {
        $metadata = [
            'row_level_security' => [
                'enabled' => true,
                'owner_field' => 'user_id',
                'owner_can_edit' => true,
                'owner_can_delete' => false
            ]
        ];
        
        $this->setTableComment($metadata);
        $tm = new TableMetadata($this->pdo, 'test_metadata');

        $this->assertTrue($tm->hasRowLevelSecurity());
        $this->assertEquals($metadata['row_level_security'], $tm->getRowLevelSecurity());
    }

    public function testNoPermissions(): void
    {
        $tm = new TableMetadata($this->pdo, 'test_metadata');

        $this->assertFalse($tm->hasPermissions());
        $this->assertEquals([], $tm->getPermissions());
    }

    public function testNoRowLevelSecurity(): void
    {
        $tm = new TableMetadata($this->pdo, 'test_metadata');

        $this->assertFalse($tm->hasRowLevelSecurity());
        $this->assertNull($tm->getRowLevelSecurity());
    }

    public function testGetDisplayName(): void
    {
        $this->setTableComment(['display_name' => 'Blog Posts']);
        $tm = new TableMetadata($this->pdo, 'test_metadata');
        $this->assertEquals('Blog Posts', $tm->getDisplayName());
    }

    public function testGetIcon(): void
    {
        $this->setTableComment(['icon' => '📝']);
        $tm = new TableMetadata($this->pdo, 'test_metadata');
        $this->assertEquals('📝', $tm->getIcon());
    }

    public function testGetColor(): void
    {
        $this->setTableComment(['color' => '#667eea']);
        $tm = new TableMetadata($this->pdo, 'test_metadata');
        $this->assertEquals('#667eea', $tm->getColor());
    }

    public function testGetListColumns(): void
    {
        $this->setTableComment(['list_view' => ['columns' => ['id', 'title', 'status']]]);
        $tm = new TableMetadata($this->pdo, 'test_metadata');
        $this->assertEquals(['id', 'title', 'status'], $tm->getListColumns());
    }

    public function testGetSearchableFields(): void
    {
        $this->setTableComment(['list_view' => ['searchable' => ['title', 'content']]]);
        $tm = new TableMetadata($this->pdo, 'test_metadata');
        $this->assertEquals(['title', 'content'], $tm->getSearchableFields());
    }

    public function testGetPerPage(): void
    {
        $this->setTableComment(['list_view' => ['per_page' => 50]]);
        $tm = new TableMetadata($this->pdo, 'test_metadata');
        $this->assertEquals(50, $tm->getPerPage());
    }

    public function testGetDefaultSort(): void
    {
        $this->setTableComment(['list_view' => ['default_sort' => 'created_at DESC']]);
        $tm = new TableMetadata($this->pdo, 'test_metadata');
        $this->assertEquals('created_at DESC', $tm->getDefaultSort());
    }

    public function testGetActions(): void
    {
        $this->setTableComment(['list_view' => ['actions' => ['edit', 'delete', 'view']]]);
        $tm = new TableMetadata($this->pdo, 'test_metadata');
        $this->assertEquals(['edit', 'delete', 'view'], $tm->getActions());
    }

    public function testGetFilters(): void
    {
        $filters = [
            ['field' => 'status', 'type' => 'select', 'options' => ['draft', 'published']]
        ];
        $this->setTableComment(['filters' => $filters]);
        $tm = new TableMetadata($this->pdo, 'test_metadata');
        $this->assertEquals($filters, $tm->getFilters());
    }

    public function testGetTimestampsBehavior(): void
    {
        $this->setTableComment(['behaviors' => ['timestamps' => ['created_at' => 'created_at']]]);
        $tm = new TableMetadata($this->pdo, 'test_metadata');
        $this->assertEquals(['created_at' => 'created_at'], $tm->getTimestampFields());
    }

    public function testGetSluggableBehavior(): void
    {
        $this->setTableComment(['behaviors' => ['sluggable' => ['source' => 'title', 'target' => 'slug']]]);
        $tm = new TableMetadata($this->pdo, 'test_metadata');
        $this->assertEquals(['source' => 'title', 'target' => 'slug'], $tm->getSluggableConfig());
    }

    public function testHasCardView(): void
    {
        $this->setTableComment(['list_view' => ['card_view' => true]]);
        $tm = new TableMetadata($this->pdo, 'test_metadata');
        $this->assertTrue($tm->hasCardView());
    }

    public function testGetCardTemplate(): void
    {
        $template = '<div>{{title}}</div>';
        $this->setTableComment(['card_template' => $template]);
        $tm = new TableMetadata($this->pdo, 'test_metadata');
        $this->assertEquals($template, $tm->getCardTemplate());
    }
}
