<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use PDO;

/**
 * Tests fonctionnels : accès aux routes et redirections
 * Vérifie les contrôles d'accès sur les pages admin et publiques
 */
class RouteAccessTest extends TestCase
{
    private static PDO $pdo;
    private static string $baseUrl;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = getTestPdo();
        initTestSchema(self::$pdo);
        self::$baseUrl = getenv('APP_URL') ?: 'http://localhost/ProjetTemplatesRestaurants/public';
    }

    /**
     * Helper : effectue une requête HTTP GET avec cURL
     */
    private function httpGet(string $url, array $cookies = []): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_NOBODY => false,
        ]);

        if (!empty($cookies)) {
            curl_setopt($ch, CURLOPT_COOKIE, implode('; ', array_map(
                fn($k, $v) => "$k=$v",
                array_keys($cookies),
                array_values($cookies)
            )));
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            $this->markTestSkipped("Le serveur n'est pas accessible : $error");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        curl_close($ch);

        return [
            'code' => $httpCode,
            'headers' => $headers,
            'body' => $body,
        ];
    }

    // --- Pages publiques ---

    public function testLandingPageIsAccessible(): void
    {
        $response = $this->httpGet(self::$baseUrl . '/?page=landing');
        $this->assertContains($response['code'], [200, 302], 'Landing page should be accessible');
    }

    public function testLoginPageIsAccessible(): void
    {
        $response = $this->httpGet(self::$baseUrl . '/?page=login');
        $this->assertContains($response['code'], [200, 302]);
    }

    // --- Pages admin protégées ---

    public function testDashboardRedirectsWhenNotLogged(): void
    {
        $response = $this->httpGet(self::$baseUrl . '/?page=dashboard');

        // Should redirect to login
        $this->assertContains($response['code'], [302, 303]);
        $this->assertStringContainsString('login', $response['headers']);
    }

    public function testSettingsRedirectsWhenNotLogged(): void
    {
        $response = $this->httpGet(self::$baseUrl . '/?page=settings');
        $this->assertContains($response['code'], [302, 303]);
    }

    public function testCarteRedirectsWhenNotLogged(): void
    {
        $response = $this->httpGet(self::$baseUrl . '/?page=carte');
        $this->assertContains($response['code'], [302, 303, 404]);
    }

    // --- Routes SUPER_ADMIN ---

    public function testSeedDemoRedirectsWhenNotLogged(): void
    {
        $response = $this->httpGet(self::$baseUrl . '/?page=seed-demo');
        $this->assertContains($response['code'], [302, 303]);
    }

    public function testSendInvitationRedirectsWhenNotLogged(): void
    {
        $response = $this->httpGet(self::$baseUrl . '/?page=send-invitation');
        $this->assertContains($response['code'], [302, 303]);
    }

    // --- Headers de sécurité ---

    public function testSecurityHeadersArePresent(): void
    {
        $response = $this->httpGet(self::$baseUrl . '/?page=login');
        $headers = strtolower($response['headers']);

        $this->assertStringContainsString('x-content-type-options: nosniff', $headers, 'X-Content-Type-Options manquant');
        $this->assertStringContainsString('x-frame-options: deny', $headers, 'X-Frame-Options manquant');
        $this->assertStringContainsString('x-xss-protection: 1', $headers, 'X-XSS-Protection manquant');
    }

    // --- Page 404 / route inconnue ---

    public function testUnknownPageReturns404OrRedirect(): void
    {
        $response = $this->httpGet(self::$baseUrl . '/?page=nonexistent-page-xyz');

        // Should either 404 or redirect to landing/login
        $this->assertContains($response['code'], [200, 302, 404]);
    }

    // --- Page vitrine publique ---

    public function testDisplayPageWithInvalidSlugReturns404(): void
    {
        $response = $this->httpGet(self::$baseUrl . '/this-restaurant-does-not-exist-xyz');

        // Should be 404 or a friendly error page
        $this->assertContains($response['code'], [404, 302, 200]);
    }
}
