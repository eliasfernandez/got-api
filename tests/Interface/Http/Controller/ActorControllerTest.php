<?php

namespace App\Tests\Interface\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ActorControllerTest extends WebTestCase
{
    public function testList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/actor');

        self::assertResponseStatusCodeSame(200);
        self::assertResponseIsSuccessful();
    }

    public function testAdd(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/actor', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'name' => 'Kit Harrington JR',
            'character' => '/api/character/109',
            'link' => '/name/kit-harrington-jr/',
            'seasons' => [1]
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseIsSuccessful();
    }

    public function testEdit(): void
    {
        $client = static::createClient();
        $client->request('PUT', '/api/actor/125', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'name' => 'Kit Harrington II',
            'character' => '/api/character/109',
            'link' => '/name/kit-harrington-II/',
            'seasons' => []
        ]));

        self::assertResponseStatusCodeSame(200);
        self::assertResponseIsSuccessful();
    }

    public function testDelete(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/actor/125', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseStatusCodeSame(204);
        self::assertResponseIsSuccessful();
    }

    public function testLinkCharacter(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/actor/1/link', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ],  json_encode(['/api/character/12']));

        $content = json_decode($client->getResponse()->getContent(), true);

        self::assertSame('Areo Hotah', $content['character']);

        self::assertResponseStatusCodeSame(200);

        $client->request('GET', '/api/actor/1', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $content = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('Areo Hotah', $content['character']);

        self::assertResponseStatusCodeSame(200);
    }
}
