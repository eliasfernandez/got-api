<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CharacterControllerTest extends WebTestCase
{
    public function testList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/character');

        self::assertResponseStatusCodeSame(200);
        self::assertResponseIsSuccessful();
    }

    public function testShow(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/character/1');

        self::assertResponseStatusCodeSame(200);
        self::assertResponseIsSuccessful();
    }

    public function testNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/character/99999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testAdd(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/character', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'name' => 'Kilian Snow',
            'actors' => [],
            'link' => '/character/kilian-snow/',
            'royal' => true,
            'thumbnail' => 'https://images-na.ssl-images-amazon.com/images/M/MV5BMTkwMjUxMDk2OV5BMl5BanBnXkFtZTcwMzg3MTg4OQ@@._V1._SX100_SY140_.jpg',
            'image' => 'https://images-na.ssl-images-amazon.com/images/M/MV5BMTkwMjUxMDk2OV5BMl5BanBnXkFtZTcwMzg3MTg4OQ@@._V1_.jpg',
        ]));

        self::assertResponseStatusCodeSame(201);
        self::assertResponseIsSuccessful();
    }

    public function testAddOnResourceFails(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/character/1', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ], '{}');

        self::assertResponseStatusCodeSame(405);
    }

    public function testAddDuplicate(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/character', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'name' => 'Jon Snow',
            'actors' => [],
            'link' => '/character/ch0155777/',
            'royal' => true,
            'thumbnail' => 'https://images-na.ssl-images-amazon.com/images/M/MV5BMTkwMjUxMDk2OV5BMl5BanBnXkFtZTcwMzg3MTg4OQ@@._V1._SX100_SY140_.jpg',
            'image' => 'https://images-na.ssl-images-amazon.com/images/M/MV5BMTkwMjUxMDk2OV5BMl5BanBnXkFtZTcwMzg3MTg4OQ@@._V1_.jpg',
        ]));

        self::assertResponseStatusCodeSame(409);
    }

    public function testEdit(): void
    {
        $client = static::createClient();
        $client->request('PUT', '/api/character/109', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'name' => 'Jon Snow II',
            'actors' => [],
            'link' => '/character/ch0155777/',
            'royal' => true,
            'thumbnail' => 'https://images-na.ssl-images-amazon.com/images/M/MV5BMTkwMjUxMDk2OV5BMl5BanBnXkFtZTcwMzg3MTg4OQ@@._V1._SX100_SY140_.jpg',
            'image' => 'https://images-na.ssl-images-amazon.com/images/M/MV5BMTkwMjUxMDk2OV5BMl5BanBnXkFtZTcwMzg3MTg4OQ@@._V1_.jpg',
        ]));

        self::assertResponseStatusCodeSame(200);
        self::assertResponseIsSuccessful();
    }

    public function testEditOnNotFound(): void
    {
        $client = static::createClient();
        $client->request('PUT', '/api/character/99999999', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'name' => 'Jon Snow II',
            'actors' => [],
            'link' => '/character/ch0155777/',
            'royal' => true,
            'thumbnail' => 'https://images-na.ssl-images-amazon.com/images/M/MV5BMTkwMjUxMDk2OV5BMl5BanBnXkFtZTcwMzg3MTg4OQ@@._V1._SX100_SY140_.jpg',
            'image' => 'https://images-na.ssl-images-amazon.com/images/M/MV5BMTkwMjUxMDk2OV5BMl5BanBnXkFtZTcwMzg3MTg4OQ@@._V1_.jpg',
        ]));

        self::assertResponseStatusCodeSame(404);
    }

    public function testDelete(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/character/109', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseStatusCodeSame(204);
        self::assertResponseIsSuccessful();

        // Check orphan relationship
        $client->request('GET', '/api/actor/124', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);
        self::assertResponseIsSuccessful();
    }

    public function testDeleteOnNotFound(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/character/99999999', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testLinkActors(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/character/1/link', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            '/api/actor/1',
            '/api/actor/2',
            '/api/actor/3',
        ]));

        $content = json_decode($client->getResponse()->getContent(), true);
        self::assertCount(3, $content['actors']);

        self::assertResponseStatusCodeSame(200);

        $client->request('GET', '/api/character/1', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $content = json_decode($client->getResponse()->getContent(), true);
        self::assertCount(3, $content['actors']);

        self::assertResponseStatusCodeSame(200);
    }

}
