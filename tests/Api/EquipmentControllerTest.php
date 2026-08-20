<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EquipmentControllerTest extends WebTestCase
{
    public function testListEquipmentReturnsSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/equipment');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testShowUnknownEquipmentReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/equipment/999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testCreateEquipmentReturns201(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/equipment',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Test Equipment API',
                'serialNumber' => 'TEST-EQ-API-001',
                'type' => 'controller',
                'status' => 'active',
                'siteId' => 1,
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);
    }

    public function testUpdateEquipmentReturns200(): void
    {
        $client = static::createClient();
        $client->request(
            'PUT',
            '/api/equipment/1',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'status' => 'maintenance',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseIsSuccessful();
    }
}