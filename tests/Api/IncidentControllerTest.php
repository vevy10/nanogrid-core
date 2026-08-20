<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class IncidentControllerTest extends WebTestCase
{
    public function testListIncidentsReturnsSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/incidents');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testShowUnknownIncidentReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/incidents/999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testCreateIncidentReturns201(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/incidents',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'title' => 'API Incident Test',
                'description' => 'Incident created by automated test.',
                'severity' => 'high',
                'status' => 'open',
                'siteId' => 1,
                'equipmentId' => 1,
                'reportedAt' => '2026-08-20T10:30:00+00:00',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);
    }
}