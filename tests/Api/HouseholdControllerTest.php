<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HouseholdControllerTest extends WebTestCase
{
    public function testListHouseholdsReturnsSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/households');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testShowUnknownHouseholdReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/households/999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testCreateHouseholdReturns201(): void
    {
        $client = static::createClient();

        $uniqueReference = 'HH-TEST-' . bin2hex(random_bytes(4));

        $client->request(
            'POST',
            '/api/households',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'reference' => $uniqueReference,
                'ownerName' => 'Test Household API',
                'phoneNumber' => '+261340000999',
                'connectionStatus' => 'connected',
                'connectedAt' => '2026-08-15T09:00:00+00:00',
                'siteId' => 1,
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);
    }
}