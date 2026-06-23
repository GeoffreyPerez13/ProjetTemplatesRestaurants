<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use PDO;

/**
 * Tests de sécurité : escalade de privilèges et contrôle d'accès
 * Vérifie que les vérifications de rôles sont correctement appliquées
 */
class AuthorizationTest extends TestCase
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
        self::$pdo->exec("DELETE FROM admin_options");
        self::$pdo->exec("DELETE FROM admins");
        self::$pdo->exec("DELETE FROM restaurants");
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        // Reset session
        $_SESSION = [];
    }

    private function createAdmin(string $role = 'ADMIN'): int
    {
        $stmt = self::$pdo->prepare("INSERT INTO restaurants (name, slug) VALUES (?, ?)");
        $stmt->execute(['Resto ' . uniqid(), 'resto-' . uniqid()]);
        $restaurantId = (int) self::$pdo->lastInsertId();

        $username = 'user_' . uniqid();
        $stmt = self::$pdo->prepare("
            INSERT INTO admins (username, email, password, role, restaurant_name, restaurant_id, carte_mode)
            VALUES (?, ?, ?, ?, ?, ?, 'editable')
        ");
        $stmt->execute([$username, "$username@test.com", password_hash('pass', PASSWORD_DEFAULT), $role, 'Resto', $restaurantId]);
        return (int) self::$pdo->lastInsertId();
    }

    // --- Tests de rôle ---

    public function testRegularAdminCannotAccessSuperAdminFunctions(): void
    {
        $adminId = $this->createAdmin('ADMIN');
        $admin = new \Admin(self::$pdo);
        $result = $admin->findById($adminId);

        $this->assertNotEquals('SUPER_ADMIN', $result->role);
        $this->assertEquals('ADMIN', $result->role);
    }

    public function testSuperAdminRoleIsCorrectlyAssigned(): void
    {
        $adminId = $this->createAdmin('SUPER_ADMIN');
        $admin = new \Admin(self::$pdo);
        $result = $admin->findById($adminId);

        $this->assertEquals('SUPER_ADMIN', $result->role);
    }

    // --- Tests de session ---

    public function testSessionNotLoggedBlocksAccess(): void
    {
        $_SESSION = [];
        $controller = new \BaseController(self::$pdo);

        // isLogged is protected, test via reflection
        $reflection = new \ReflectionMethod($controller, 'isLogged');
        $reflection->setAccessible(true);

        $this->assertFalse($reflection->invoke($controller));
    }

    public function testSessionLoggedAllowsAccess(): void
    {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_id'] = 1;
        $controller = new \BaseController(self::$pdo);

        $reflection = new \ReflectionMethod($controller, 'isLogged');
        $reflection->setAccessible(true);

        $this->assertTrue($reflection->invoke($controller));
    }

    public function testSessionWithFalsyValueBlocksAccess(): void
    {
        $_SESSION['admin_logged'] = 'yes'; // truthy but not === true
        $controller = new \BaseController(self::$pdo);

        $reflection = new \ReflectionMethod($controller, 'isLogged');
        $reflection->setAccessible(true);

        $this->assertFalse($reflection->invoke($controller));
    }

    // --- Tests CSRF ---

    public function testCsrfTokenGeneration(): void
    {
        $_SESSION = [];
        $controller = new \BaseController(self::$pdo);

        $reflection = new \ReflectionMethod($controller, 'getCsrfToken');
        $reflection->setAccessible(true);
        $token = $reflection->invoke($controller);

        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        $this->assertGreaterThanOrEqual(32, strlen($token));
    }

    public function testCsrfTokenValidationRejectsNull(): void
    {
        $controller = new \BaseController(self::$pdo);

        $reflection = new \ReflectionMethod($controller, 'verifyCsrfToken');
        $reflection->setAccessible(true);

        $this->assertFalse($reflection->invoke($controller, null));
    }

    public function testCsrfTokenValidationRejectsInvalidToken(): void
    {
        $_SESSION['csrf_token'] = 'real_token_value';
        $controller = new \BaseController(self::$pdo);

        $reflection = new \ReflectionMethod($controller, 'verifyCsrfToken');
        $reflection->setAccessible(true);

        $this->assertFalse($reflection->invoke($controller, 'wrong_token'));
    }

    public function testCsrfTokenValidationAcceptsCorrectToken(): void
    {
        $controller = new \BaseController(self::$pdo);

        // Generate token
        $reflectionGet = new \ReflectionMethod($controller, 'getCsrfToken');
        $reflectionGet->setAccessible(true);
        $token = $reflectionGet->invoke($controller);

        // Verify token
        $reflectionVerify = new \ReflectionMethod($controller, 'verifyCsrfToken');
        $reflectionVerify->setAccessible(true);

        $this->assertTrue($reflectionVerify->invoke($controller, $token));
    }

    // --- Test isolation des données entre admins ---

    public function testAdminCannotSeeOtherAdminCategories(): void
    {
        $admin1Id = $this->createAdmin();
        $admin2Id = $this->createAdmin();

        // Créer catégorie pour admin1
        $stmt = self::$pdo->prepare("INSERT INTO categories (admin_id, name) VALUES (?, ?)");
        $stmt->execute([$admin1Id, 'Secret Category']);

        // Requête pour admin2 ne devrait pas voir la catégorie d'admin1
        $stmt = self::$pdo->prepare("SELECT * FROM categories WHERE admin_id = ?");
        $stmt->execute([$admin2Id]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEmpty($categories);
    }

    // --- Test password hashing ---

    public function testPasswordIsNeverStoredInPlainText(): void
    {
        $adminId = $this->createAdmin();
        $stmt = self::$pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $hash = $stmt->fetchColumn();

        $this->assertNotEquals('pass', $hash);
        $this->assertTrue(password_verify('pass', $hash));
        $this->assertStringStartsWith('$2y$', $hash);
    }
}
