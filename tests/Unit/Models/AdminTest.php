<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use PDO;

class AdminTest extends TestCase
{
    private static PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = getTestPdo();
        initTestSchema(self::$pdo);
    }

    protected function setUp(): void
    {
        self::$pdo->exec("DELETE FROM admins");
        self::$pdo->exec("DELETE FROM restaurants");
    }

    private function createTestRestaurant(string $name = 'Test Restaurant', string $slug = 'test-restaurant'): int
    {
        $stmt = self::$pdo->prepare("INSERT INTO restaurants (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        return (int) self::$pdo->lastInsertId();
    }

    private function createTestAdmin(array $overrides = []): int
    {
        $restaurantId = $overrides['restaurant_id'] ?? $this->createTestRestaurant();
        $defaults = [
            'username' => 'testadmin',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'ADMIN',
            'restaurant_name' => 'Test Restaurant',
            'restaurant_id' => $restaurantId,
            'carte_mode' => 'editable',
        ];
        $data = array_merge($defaults, $overrides);

        $stmt = self::$pdo->prepare("
            INSERT INTO admins (username, email, password, role, restaurant_name, restaurant_id, carte_mode)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['username'], $data['email'], $data['password'],
            $data['role'], $data['restaurant_name'], $data['restaurant_id'], $data['carte_mode'],
        ]);
        return (int) self::$pdo->lastInsertId();
    }

    // --- Tests findById ---

    public function testFindByIdReturnsAdmin(): void
    {
        $id = $this->createTestAdmin();
        $admin = new \Admin(self::$pdo);
        $result = $admin->findById($id);

        $this->assertNotFalse($result);
        $this->assertEquals('testadmin', $result->username);
        $this->assertEquals('test@example.com', $result->email);
        $this->assertEquals('ADMIN', $result->role);
    }

    public function testFindByIdReturnsNullForNonExistent(): void
    {
        $admin = new \Admin(self::$pdo);
        $result = $admin->findById(99999);

        $this->assertNull($result);
    }

    // --- Tests findByUsername ---

    public function testFindByUsernameReturnsAdmin(): void
    {
        $this->createTestAdmin(['username' => 'findme']);
        $admin = new \Admin(self::$pdo);
        $result = $admin->findByUsername('findme');

        $this->assertNotFalse($result);
        $this->assertEquals('findme', $result->username);
    }

    public function testFindByUsernameReturnsNullForNonExistent(): void
    {
        $admin = new \Admin(self::$pdo);
        $result = $admin->findByUsername('nonexistent');

        $this->assertNull($result);
    }

    // --- Tests rôles ---

    public function testSuperAdminRoleIsStored(): void
    {
        $id = $this->createTestAdmin(['username' => 'superadmin', 'role' => 'SUPER_ADMIN']);
        $admin = new \Admin(self::$pdo);
        $result = $admin->findById($id);

        $this->assertEquals('SUPER_ADMIN', $result->role);
    }

    // --- Tests password ---

    public function testPasswordIsHashedCorrectly(): void
    {
        $plainPassword = 'SecurePass123!';
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $this->createTestAdmin(['username' => 'hashtest', 'password' => $hash]);

        $admin = new \Admin(self::$pdo);
        $result = $admin->findByUsername('hashtest');

        $this->assertTrue(password_verify($plainPassword, $result->password));
        $this->assertFalse(password_verify('wrongpassword', $result->password));
    }

    // --- Tests updatePassword ---

    public function testVerifyPasswordWorksCorrectly(): void
    {
        $plainPassword = 'MySecret!456';
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $this->createTestAdmin(['username' => 'pwdverify', 'password' => $hash]);

        $admin = new \Admin(self::$pdo);
        $result = $admin->findByUsername('pwdverify');

        $this->assertTrue($result->verifyPassword($plainPassword));
        $this->assertFalse($result->verifyPassword('wrongpassword'));
    }

    public function testLoginReturnsAdminOnValidCredentials(): void
    {
        $hash = password_hash('logintest123', PASSWORD_DEFAULT);
        $this->createTestAdmin(['username' => 'loginuser', 'password' => $hash]);

        $admin = new \Admin(self::$pdo);
        $result = $admin->login('loginuser', 'logintest123');

        $this->assertNotNull($result);
        $this->assertEquals('loginuser', $result->username);
    }

    public function testLoginReturnsNullOnInvalidCredentials(): void
    {
        $hash = password_hash('correctpass', PASSWORD_DEFAULT);
        $this->createTestAdmin(['username' => 'loginfail', 'password' => $hash]);

        $admin = new \Admin(self::$pdo);
        $result = $admin->login('loginfail', 'wrongpass');

        $this->assertNull($result);
    }

    // --- Tests restaurant association ---

    public function testAdminIsLinkedToRestaurant(): void
    {
        $restaurantId = $this->createTestRestaurant('Mon Resto', 'mon-resto');
        $id = $this->createTestAdmin([
            'username' => 'linked',
            'restaurant_id' => $restaurantId,
            'restaurant_name' => 'Mon Resto',
        ]);

        $admin = new \Admin(self::$pdo);
        $result = $admin->findById($id);

        $this->assertEquals($restaurantId, $result->restaurant_id);
        $this->assertEquals('Mon Resto', $result->restaurant_name);
    }
}
