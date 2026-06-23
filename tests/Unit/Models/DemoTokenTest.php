<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use PDO;

class DemoTokenTest extends TestCase
{
    private static PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = getTestPdo();
        initTestSchema(self::$pdo);
    }

    protected function setUp(): void
    {
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        self::$pdo->exec("DELETE FROM demo_tokens");
        self::$pdo->exec("DELETE FROM categories");
        self::$pdo->exec("DELETE FROM admin_options");
        self::$pdo->exec("DELETE FROM contact");
        self::$pdo->exec("DELETE FROM admins");
        self::$pdo->exec("DELETE FROM restaurants");
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }

    private function seedDemoTemplate(): int
    {
        $stmt = self::$pdo->prepare("INSERT INTO restaurants (name, slug) VALUES (?, ?)");
        $stmt->execute(['Le Bistrot MenuCraft', 'demo-menucraft']);
        $restaurantId = (int) self::$pdo->lastInsertId();

        $stmt = self::$pdo->prepare("
            INSERT INTO admins (username, email, password, role, restaurant_name, restaurant_id, carte_mode)
            VALUES (?, ?, ?, 'SUPER_ADMIN', ?, ?, 'editable')
        ");
        $stmt->execute(['demo_admin', 'demo@menucraft.com', password_hash('demo', PASSWORD_DEFAULT), 'Le Bistrot MenuCraft', $restaurantId]);
        $adminId = (int) self::$pdo->lastInsertId();

        // Options
        $stmt = self::$pdo->prepare("INSERT INTO admin_options (admin_id, option_name, option_value) VALUES (?, ?, ?)");
        $stmt->execute([$adminId, 'site_online', '1']);

        // Catégorie + plat
        $stmt = self::$pdo->prepare("INSERT INTO categories (admin_id, name) VALUES (?, ?)");
        $stmt->execute([$adminId, 'Entrées']);
        $catId = (int) self::$pdo->lastInsertId();

        $stmt = self::$pdo->prepare("INSERT INTO plats (category_id, name, description, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$catId, 'Soupe à l\'oignon', 'Gratinée au fromage', 8.50]);

        // Contact
        $stmt = self::$pdo->prepare("INSERT INTO contact (admin_id, telephone, email, adresse) VALUES (?, ?, ?, ?)");
        $stmt->execute([$adminId, '0145678900', 'contact@bistrot.com', '1 Rue de Paris']);

        return $adminId;
    }

    private function createSuperAdmin(): int
    {
        $stmt = self::$pdo->prepare("INSERT INTO restaurants (name, slug) VALUES (?, ?)");
        $stmt->execute(['Admin Site', 'admin-site']);
        $restaurantId = (int) self::$pdo->lastInsertId();

        $stmt = self::$pdo->prepare("
            INSERT INTO admins (username, email, password, role, restaurant_name, restaurant_id, carte_mode)
            VALUES (?, ?, ?, 'SUPER_ADMIN', ?, ?, 'editable')
        ");
        $stmt->execute(['superadmin', 'super@menucraft.com', password_hash('admin', PASSWORD_DEFAULT), 'Admin', $restaurantId]);
        return (int) self::$pdo->lastInsertId();
    }

    // --- getDemoAdminId ---

    public function testGetDemoAdminIdReturnsIdWhenDemoExists(): void
    {
        $expectedId = $this->seedDemoTemplate();
        $model = new \DemoToken(self::$pdo);

        $result = $model->getDemoAdminId();
        $this->assertEquals($expectedId, $result);
    }

    public function testGetDemoAdminIdReturnsFalseWhenNoDemoExists(): void
    {
        $model = new \DemoToken(self::$pdo);
        $result = $model->getDemoAdminId();

        $this->assertFalse($result);
    }

    // --- generate ---

    public function testGenerateCreatesTokenAndClone(): void
    {
        $this->seedDemoTemplate();
        $superAdminId = $this->createSuperAdmin();
        $model = new \DemoToken(self::$pdo);

        $result = $model->generate($superAdminId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('admin_id', $result);
        $this->assertArrayHasKey('expires_at', $result);
        $this->assertArrayHasKey('slug', $result);
        $this->assertStringStartsWith('demo-menucraft-', $result['slug']);
        $this->assertEquals(64, strlen($result['token']));
    }

    public function testGenerateReturnsFalseIfNoDemoTemplate(): void
    {
        $superAdminId = $this->createSuperAdmin();
        $model = new \DemoToken(self::$pdo);

        $result = $model->generate($superAdminId);
        $this->assertFalse($result);
    }

    public function testGenerateClonesCategories(): void
    {
        $this->seedDemoTemplate();
        $superAdminId = $this->createSuperAdmin();
        $model = new \DemoToken(self::$pdo);

        $result = $model->generate($superAdminId);
        $cloneAdminId = $result['admin_id'];

        $stmt = self::$pdo->prepare("SELECT * FROM categories WHERE admin_id = ?");
        $stmt->execute([$cloneAdminId]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(1, $categories);
        $this->assertEquals('Entrées', $categories[0]['name']);
    }

    public function testGenerateClonesPlats(): void
    {
        $this->seedDemoTemplate();
        $superAdminId = $this->createSuperAdmin();
        $model = new \DemoToken(self::$pdo);

        $result = $model->generate($superAdminId);
        $cloneAdminId = $result['admin_id'];

        $stmt = self::$pdo->prepare("
            SELECT p.* FROM plats p
            JOIN categories c ON c.id = p.category_id
            WHERE c.admin_id = ?
        ");
        $stmt->execute([$cloneAdminId]);
        $plats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(1, $plats);
        $this->assertEquals('Soupe à l\'oignon', $plats[0]['name']);
        $this->assertEquals(8.50, (float) $plats[0]['price']);
    }

    // --- validate ---

    public function testValidateReturnsDataForValidToken(): void
    {
        $this->seedDemoTemplate();
        $superAdminId = $this->createSuperAdmin();
        $model = new \DemoToken(self::$pdo);

        $generated = $model->generate($superAdminId);
        $result = $model->validate($generated['token']);

        $this->assertIsArray($result);
        $this->assertEquals($generated['admin_id'], $result['admin_id']);
    }

    public function testValidateReturnsFalseForExpiredToken(): void
    {
        $this->seedDemoTemplate();
        $superAdminId = $this->createSuperAdmin();
        $model = new \DemoToken(self::$pdo);

        $generated = $model->generate($superAdminId);

        // Forcer expiration
        $stmt = self::$pdo->prepare("UPDATE demo_tokens SET expires_at = '2020-01-01 00:00:00' WHERE token = ?");
        $stmt->execute([$generated['token']]);

        $result = $model->validate($generated['token']);
        $this->assertFalse($result);
    }

    public function testValidateReturnsFalseForInvalidToken(): void
    {
        $model = new \DemoToken(self::$pdo);
        $result = $model->validate('invalid_token_that_does_not_exist');

        $this->assertFalse($result);
    }

    // --- delete ---

    public function testDeleteRemovesTokenAndClone(): void
    {
        $this->seedDemoTemplate();
        $superAdminId = $this->createSuperAdmin();
        $model = new \DemoToken(self::$pdo);

        $generated = $model->generate($superAdminId);
        $model->delete($generated['id']);

        // Token should be gone
        $result = $model->validate($generated['token']);
        $this->assertFalse($result);

        // Cloned admin should be gone
        $stmt = self::$pdo->prepare("SELECT id FROM admins WHERE id = ?");
        $stmt->execute([$generated['admin_id']]);
        $this->assertFalse($stmt->fetchColumn());
    }

    // --- cleanExpired ---

    public function testCleanExpiredRemovesOnlyExpiredTokens(): void
    {
        $this->seedDemoTemplate();
        $superAdminId = $this->createSuperAdmin();
        $model = new \DemoToken(self::$pdo);

        $token1 = $model->generate($superAdminId);
        $token2 = $model->generate($superAdminId);

        // Expire token1
        $stmt = self::$pdo->prepare("UPDATE demo_tokens SET expires_at = '2020-01-01 00:00:00' WHERE id = ?");
        $stmt->execute([$token1['id']]);

        $cleaned = $model->cleanExpired();
        $this->assertEquals(1, $cleaned);

        // token2 should still be valid
        $this->assertIsArray($model->validate($token2['token']));
        // token1 should be gone
        $this->assertFalse($model->validate($token1['token']));
    }

    // --- getActiveTokens ---

    public function testGetActiveTokensReturnsOnlyNonExpired(): void
    {
        $this->seedDemoTemplate();
        $superAdminId = $this->createSuperAdmin();
        $model = new \DemoToken(self::$pdo);

        $model->generate($superAdminId);
        $token2 = $model->generate($superAdminId);

        // Expire one
        $stmt = self::$pdo->prepare("UPDATE demo_tokens SET expires_at = '2020-01-01 00:00:00' WHERE id = ?");
        $stmt->execute([$token2['id']]);

        $active = $model->getActiveTokens();
        $this->assertCount(1, $active);
    }

    // --- updateLabel ---

    public function testUpdateLabelSetsLabel(): void
    {
        $this->seedDemoTemplate();
        $superAdminId = $this->createSuperAdmin();
        $model = new \DemoToken(self::$pdo);

        $generated = $model->generate($superAdminId);
        $model->updateLabel($generated['id'], 'Client Test');

        $stmt = self::$pdo->prepare("SELECT label FROM demo_tokens WHERE id = ?");
        $stmt->execute([$generated['id']]);
        $label = $stmt->fetchColumn();

        $this->assertEquals('Client Test', $label);
    }
}
