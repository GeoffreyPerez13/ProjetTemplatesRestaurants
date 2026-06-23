<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use PDO;

class RestaurantTest extends TestCase
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

    private function createRestaurant(string $name = 'Le Bistrot', string $slug = 'le-bistrot'): int
    {
        $stmt = self::$pdo->prepare("INSERT INTO restaurants (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        return (int) self::$pdo->lastInsertId();
    }

    // --- findBySlug ---

    public function testFindBySlugReturnsRestaurant(): void
    {
        $this->createRestaurant('Le Bistrot', 'le-bistrot');
        $model = new \Restaurant(self::$pdo);
        $result = $model->findBySlug('le-bistrot');

        $this->assertNotFalse($result);
        $this->assertEquals('Le Bistrot', $result->name);
        $this->assertEquals('le-bistrot', $result->slug);
    }

    public function testFindBySlugReturnsFalseForNonExistent(): void
    {
        $model = new \Restaurant(self::$pdo);
        $result = $model->findBySlug('non-existent-slug');

        $this->assertFalse($result);
    }

    // --- updateTimestamp ---

    public function testUpdateTimestampModifiesUpdatedAt(): void
    {
        $id = $this->createRestaurant();
        $model = new \Restaurant(self::$pdo);

        // Attendre 1s pour que le timestamp soit différent
        sleep(1);
        $model->updateTimestamp($id);

        $stmt = self::$pdo->prepare("SELECT updated_at FROM restaurants WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        $this->assertNotNull($result->updated_at);
    }

    // --- getLastUpdate ---

    public function testGetLastUpdateReturnsDate(): void
    {
        $id = $this->createRestaurant();
        $model = new \Restaurant(self::$pdo);
        $result = $model->getLastUpdate($id);

        $this->assertNotNull($result);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}/', $result);
    }

    public function testGetLastUpdateReturnsNullForNonExistent(): void
    {
        $model = new \Restaurant(self::$pdo);
        $result = $model->getLastUpdate(99999);

        $this->assertNull($result);
    }

    // --- Slug unique constraint ---

    public function testSlugMustBeUnique(): void
    {
        $this->createRestaurant('Restaurant A', 'unique-slug');

        $this->expectException(\PDOException::class);
        $this->createRestaurant('Restaurant B', 'unique-slug');
    }
}
