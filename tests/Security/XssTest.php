<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use PDO;

/**
 * Tests de sécurité : Cross-Site Scripting (XSS)
 * Vérifie que les données sont correctement échappées via htmlspecialchars
 */
class XssTest extends TestCase
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
        self::$pdo->exec("DELETE FROM categories");
    }

    /**
     * Test que htmlspecialchars échappe correctement les payloads XSS
     * @dataProvider xssPayloads
     */
    public function testHtmlspecialcharsEscapesXss(string $payload): void
    {
        $escaped = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');

        // Les caractères structurels HTML doivent être échappés
        $this->assertStringNotContainsString('<', $escaped, 'Le caractère < ne doit pas être présent brut');
        $this->assertStringNotContainsString('>', $escaped, 'Le caractère > ne doit pas être présent brut');
        // Les guillemets doivent être échappés (empêche l\'injection d\'attributs)
        $this->assertStringNotContainsString('"', $escaped, 'Les guillemets doivent être encodés');
    }

    /**
     * Test que les noms de restaurant avec XSS sont stockés/récupérés correctement
     * (les données brutes sont ok en DB, mais doivent être échappées à l'affichage)
     */
    public function testRestaurantNameWithXssIsStoredRaw(): void
    {
        $xssName = '<script>alert("xss")</script>';
        $stmt = self::$pdo->prepare("INSERT INTO restaurants (name, slug) VALUES (?, ?)");
        $stmt->execute([$xssName, 'xss-test']);

        $restaurant = new \Restaurant(self::$pdo);
        $result = $restaurant->findBySlug('xss-test');

        // La donnée brute est stockée telle quelle
        $this->assertEquals($xssName, $result->name);

        // Mais à l'affichage, htmlspecialchars doit neutraliser le script
        $displayed = htmlspecialchars($result->name, ENT_QUOTES, 'UTF-8');
        $this->assertStringNotContainsString('<script>', $displayed);
        $this->assertStringContainsString('&lt;script&gt;', $displayed);
    }

    /**
     * Test que les noms de catégorie avec XSS sont correctement échappés
     */
    public function testCategoryNameXssEscaping(): void
    {
        $stmt = self::$pdo->prepare("INSERT INTO restaurants (name, slug) VALUES (?, ?)");
        $stmt->execute(['Resto', 'resto-xss']);
        $restaurantId = (int) self::$pdo->lastInsertId();

        $stmt = self::$pdo->prepare("
            INSERT INTO admins (username, email, password, role, restaurant_name, restaurant_id, carte_mode)
            VALUES (?, ?, ?, 'ADMIN', ?, ?, 'editable')
        ");
        $stmt->execute(['xssadmin', 'xss@test.com', password_hash('p', PASSWORD_DEFAULT), 'Resto', $restaurantId]);
        $adminId = (int) self::$pdo->lastInsertId();

        $xssPayload = '"><img src=x onerror=alert(1)>';
        $stmt = self::$pdo->prepare("INSERT INTO categories (admin_id, name) VALUES (?, ?)");
        $stmt->execute([$adminId, $xssPayload]);

        $stmt = self::$pdo->prepare("SELECT name FROM categories WHERE admin_id = ?");
        $stmt->execute([$adminId]);
        $name = $stmt->fetchColumn();

        $escaped = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        // Les guillemets et chevrons sont échappés, empêchant l'exécution
        $this->assertStringNotContainsString('<', $escaped);
        $this->assertStringNotContainsString('>', $escaped);
        $this->assertStringNotContainsString('"', $escaped);
        $this->assertStringContainsString('&quot;', $escaped);
        $this->assertStringContainsString('&gt;', $escaped);
    }

    /**
     * Test que le slug est sûr (pas de caractères dangereux après validation)
     */
    public function testSlugSanitization(): void
    {
        $dangerousSlug = '<script>alert(1)</script>';
        $safeSlug = preg_replace('/[^a-z0-9\-]/', '', strtolower($dangerousSlug));

        $this->assertStringNotContainsString('<', $safeSlug);
        $this->assertStringNotContainsString('>', $safeSlug);
        $this->assertMatchesRegularExpression('/^[a-z0-9\-]*$/', $safeSlug);
    }

    /**
     * Payloads XSS classiques
     */
    public static function xssPayloads(): array
    {
        return [
            'basic script' => ['<script>alert("xss")</script>'],
            'img onerror' => ['<img src=x onerror=alert(1)>'],
            'svg onload' => ['<svg onload=alert(1)>'],
            'event handler' => ['<div onmouseover="alert(1)">hover</div>'],
            'javascript href' => ['<a href="javascript:alert(1)">click</a>'],
            'encoded script' => ['%3Cscript%3Ealert(1)%3C/script%3E'],
            'nested tags' => ['<scr<script>ipt>alert(1)</scr</script>ipt>'],
            'null byte' => ["<scri\x00pt>alert(1)</script>"],
            'attribute escape' => ['" onfocus="alert(1)" autofocus="'],
            'style injection' => ['<div style="background:url(javascript:alert(1))">'],
            'data URI' => ['<a href="data:text/html,<script>alert(1)</script>">x</a>'],
            'template literal' => ['${alert(1)}'],
        ];
    }
}
