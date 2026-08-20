<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SiteControllerTest extends WebTestCase
{
    public function testListSitesReturnsSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/sites');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testShowUnknownSiteReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/sites/999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testCreateSiteReturns201(): void
    {
        $client = static::createClient();

        $uniqueCode = 'TEST-SITE-' . bin2hex(random_bytes(4));

        $client->request(
            'POST',
            '/api/sites',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Test Site API',
                'code' => $uniqueCode,
                'region' => 'Diana',
                'status' => 'active',
                'commissionedAt' => '2026-08-01T10:00:00+00:00',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);
    }
}