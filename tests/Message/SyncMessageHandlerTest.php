<?php

namespace App\Tests\Message;

use App\Elasticsearch\QueryBuilder;
use App\Entity\Actor;
use App\Message\SyncMessage;
use App\Message\SyncMessageHandler;
use App\Repository\ActorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SyncMessageHandlerTest extends KernelTestCase
{

    private QueryBuilder $elasticsearch;
    private EntityManagerInterface $entityManager;
    private SyncMessageHandler $handler;
    private ActorRepository $actorRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->elasticsearch = self::getContainer()->get(QueryBuilder::class);
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->handler = self::getContainer()->get(SyncMessageHandler::class);
        $this->actorRepository = self::getContainer()->get(ActorRepository::class);
    }

    public function testItStoreEntityChangesInES(): void
    {
        $result = $this->elasticsearch
            ->overIndexPattern('got-test')
            ->filterByTerm('type', 'actor')
            ->filterByTerm('entity.id', 105)
            ->search('*', [])
            ->getResults();

        $this->assertCount(1, $result['hits']['hits']);
        $this->assertSame('actor-105', $result['hits']['hits'][0]['_id']);
        $this->assertSame('Christopher Newman', $result['hits']['hits'][0]['_source']['entity']['name']);

        $actor = $this->actorRepository->find(105);
        $actor->setName('Christopher Oldman');
        $this->entityManager->persist($actor);
        $this->entityManager->flush();

       $result = $this->elasticsearch
            ->overIndexPattern('got-test')
            ->filterByTerm('type', 'actor')
            ->filterByTerm('entity.id', 105)
            ->search('*', [])
            ->getResults(false);

        $this->assertCount(1, $result['hits']['hits']);
        $this->assertSame('actor-105', $result['hits']['hits'][0]['_id']);
        $this->assertSame('Christopher Oldman', $result['hits']['hits'][0]['_source']['entity']['name']);
    }

    public function testItHandlesMissingEntity(): void
    {

        $message = new SyncMessage(Actor::class, 999999);

        $this->handler->__invoke($message);

        $this->assertTrue(true);
    }
}