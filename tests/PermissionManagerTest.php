<?php

namespace DynamicCRUD\Tests;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\Security\PermissionManager;
use PDO;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PermissionManagerTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = TestHelper::getPDO();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function testTablePermissionGrantsAccess(): void
    {
        $metadata = [
            'permissions' => [
                'create' => ['admin', 'editor'],
                'update' => ['admin', 'editor'],
                'delete' => ['admin']
            ]
        ];

        $pm = new PermissionManager($this->pdo, 'posts', $metadata);
        $pm->setCurrentUser(1, 'admin');

        $this->assertTrue($pm->canCreate());
        $this->assertTrue($pm->canUpdate());
        $this->assertTrue($pm->canDelete());
    }

    public function testTablePermissionDeniesAccess(): void
    {
        $metadata = [
            'permissions' => [
                'create' => ['admin'],
                'update' => ['admin'],
                'delete' => ['admin']
            ]
        ];

        $pm = new PermissionManager($this->pdo, 'posts', $metadata);
        $pm->setCurrentUser(2, 'user');

        $this->assertFalse($pm->canCreate());
        $this->assertFalse($pm->canUpdate());
        $this->assertFalse($pm->canDelete());
    }

    public function testRowLevelSecurityOwnerCanEdit(): void
    {
        $metadata = [
            'permissions' => [
                'update' => ['admin']
            ],
            'row_level_security' => [
                'enabled' => true,
                'owner_field' => 'user_id',
                'owner_can_edit' => true,
                'owner_can_delete' => false
            ]
        ];

        $pm = new PermissionManager($this->pdo, 'posts', $metadata);
        $pm->setCurrentUser(3, 'author');

        $ownRecord = ['id' => 1, 'user_id' => 3];
        $otherRecord = ['id' => 2, 'user_id' => 1];

        $this->assertTrue($pm->canUpdate($ownRecord));
        $this->assertFalse($pm->canUpdate($otherRecord));
    }

    public function testRowLevelSecurityOwnerCannotDelete(): void
    {
        $metadata = [
            'permissions' => [
                'delete' => ['admin']
            ],
            'row_level_security' => [
                'enabled' => true,
                'owner_field' => 'user_id',
                'owner_can_edit' => true,
                'owner_can_delete' => false
            ]
        ];

        $pm = new PermissionManager($this->pdo, 'posts', $metadata);
        $pm->setCurrentUser(3, 'author');

        $ownRecord = ['id' => 1, 'user_id' => 3];

        $this->assertFalse($pm->canDelete($ownRecord));
    }

    public function testWildcardPermissionGrantsAccessToAll(): void
    {
        $metadata = [
            'permissions' => [
                'read' => ['*']
            ]
        ];

        $pm = new PermissionManager($this->pdo, 'posts', $metadata);
        $pm->setCurrentUser(999, 'guest');

        $this->assertTrue($pm->canRead());
    }

    public function testNoPermissionsConfiguredGrantsAccess(): void
    {
        $pm = new PermissionManager($this->pdo, 'posts', []);
        $pm->setCurrentUser(1, 'user');

        $this->assertTrue($pm->canCreate());
        $this->assertTrue($pm->canRead());
        $this->assertTrue($pm->canUpdate());
        $this->assertTrue($pm->canDelete());
    }

    public function testGetCurrentUserInfo(): void
    {
        $pm = new PermissionManager($this->pdo, 'posts', []);
        $pm->setCurrentUser(42, 'editor');

        $this->assertEquals(42, $pm->getCurrentUserId());
        $this->assertEquals('editor', $pm->getCurrentUserRole());
    }

    public function testHasRowLevelSecurity(): void
    {
        $metadata = [
            'row_level_security' => [
                'enabled' => true,
                'owner_field' => 'user_id'
            ]
        ];

        $pm = new PermissionManager($this->pdo, 'posts', $metadata);
        
        $this->assertTrue($pm->hasRowLevelSecurity());
        $this->assertIsArray($pm->getRowLevelSecurity());
    }

    public function testTablePermissionOverridesRowLevel(): void
    {
        $metadata = [
            'permissions' => [
                'update' => ['admin']
            ],
            'row_level_security' => [
                'enabled' => true,
                'owner_field' => 'user_id',
                'owner_can_edit' => true
            ]
        ];

        $pm = new PermissionManager($this->pdo, 'posts', $metadata);
        $pm->setCurrentUser(1, 'admin');

        $otherRecord = ['id' => 1, 'user_id' => 999];

        $this->assertTrue($pm->canUpdate($otherRecord));
    }
}
