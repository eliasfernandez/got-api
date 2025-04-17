<?php

namespace App\Tests\Message;

use App\Elasticsearch\QueryBuilder;
use App\Entity\Character;
use App\Message\DeleteMessage;
use App\Message\DeleteMessageHandler;
use App\Repository\CharacterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeleteMessageHandlerTest extends KernelTestCase
{
    private QueryBuilder $elasticsearch;
    private EntityManagerInterface $entityManager;
    private DeleteMessageHandler $handler;
    private CharacterRepository $characterRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->elasticsearch = self::getContainer()->get(QueryBuilder::class);
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->handler = self::getContainer()->get(DeleteMessageHandler::class);
        $this->characterRepository = self::getContainer()->get(CharacterRepository::class);
    }

    public function testItDeletesEntityFromES(): void
    {
        $character = $this->characterRepository->find(105);
        $characterId = $character->getId();
        $result = $this->elasticsearch
            ->overIndexPattern('got-test')
            ->filterByTerm('type', 'character')
            ->filterByTerm('entity.id', 105)
            ->search('*', [])
            ->getResults();

        $this->assertCount(1, $result['hits']['hits']);
        $this->assertSame('character-105', $result['hits']['hits'][0]['_id']);

        $message = new DeleteMessage(Character::class, $characterId);
        $this->handler->__invoke($message);

        $result = $this->elasticsearch
            ->overIndexPattern('got-test')
            ->filterByTerm('type', 'character')
            ->filterByTerm('entity.id', 105)
            ->search('*', [])
            ->getResults(false);

        $this->assertCount(0, $result['hits']['hits']);

        $entity = $this->characterRepository->find($characterId);
        $this->assertNotNull($entity, 'Entity should still exist in the DB');
    }

    public function testItHandlesMissingEntity(): void
    {

        $message = new DeleteMessage(Character::class, 999999);

        $this->handler->__invoke($message);

        $this->assertTrue(true);
    }
}