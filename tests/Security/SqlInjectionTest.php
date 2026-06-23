<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use PDO;

/**
 * Tests de sécurité : injection SQL
 * Vérifie que les requêtes préparées protègent contre les injections
 */
class SqlInjectionTest extends TestCase
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

        // Créer un admin de test
        $stmt = self::$pdo->prepare("INSERT INTO restaurants (name, slug) VALUES (?, ?)");
        $stmt->execute(['Test Resto', 'test-resto']);
        $restaurantId = (int) self::$pdo->lastInsertId();

        $stmt = self::$pdo->prepare("
            INSERT INTO admins (username, email, password, role, restaurant_name, restaurant_id, carte_mode)
            VALUES (?, ?, ?, 'ADMIN', ?, ?, 'editable')
        ");
        $stmt->execute(['victim', 'victim@test.com', password_hash('secret123', PASSWORD_DEFAULT), 'Test Resto', $restaurantId]);
    }

    /**
     * @dataProvider sqlInjectionPayloads
     */
    public function testFindByUsernameIsProtected(string $payload): void
    {
        $admin = new \Admin(self::$pdo);
        $result = $admin->findByUsername($payload);

        // Should not return our victim user - null means no match found (secure)
        $this->assertNull($result, "SQL injection payload should not bypass findByUsername: $payload");
    }

    /**
     * @dataProvider sqlInjectionPayloads
     */
    public function testFindBySlugIsProtected(string $payload): void
    {
        $restaurant = new \Restaurant(self::$pdo);
        $result = $restaurant->findBySlug($payload);

        $this->assertFalse($result, "SQL injection payload should not bypass findBySlug: $payload");
    }

    /**
     * @dataProvider sqlInjectionPayloads
     */
    public function testDemoTokenValidateIsProtected(string $payload): void
    {
        $model = new \DemoToken(self::$pdo);
        $result = $model->validate($payload);

        $this->assertFalse($result, "SQL injection payload should not bypass DemoToken validate: $payload");
    }

    /**
     * Payloads classiques d'injection SQL
     */
    public static function sqlInjectionPayloads(): array
    {
        return [
            'basic OR true' => ["' OR '1'='1"],
            'UNION SELECT' => ["' UNION SELECT * FROM admins--"],
            'comment bypass' => ["admin'--"],
            'stacked query' => ["'; DROP TABLE admins;--"],
            'blind boolean' => ["' AND 1=1--"],
            'blind time-based' => ["' AND SLEEP(5)--"],
            'hex encoding' => ["0x27204f522027313d2731"],
            'double quote' => ['" OR "1"="1'],
            'backslash escape' => ["\\' OR 1=1--"],
            'null byte' => ["admin\x00' OR '1'='1"],
            'nested comment' => ["admin'/**/OR/**/1=1--"],
            'LIKE injection' => ["' OR username LIKE '%"],
        ];
    }
}
