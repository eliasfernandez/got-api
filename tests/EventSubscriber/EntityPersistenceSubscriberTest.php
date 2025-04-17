<?php

namespace App\Tests\EventSubscriber;

use App\Entity\EntityInterface;
use App\Entity\House;
use App\EventSubscriber\EntityPersistenceSubscriber;
use App\Message\DeleteMessage;
use App\Message\SyncMessage;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class EntityPersistenceSubscriberTest extends TestCase
{
    private MessageBusInterface $bus;
    private EntityPersistenceSubscriber $subscriber;
    private ObjectManager $objectManager;

    protected function setUp(): void
    {
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->subscriber = new EntityPersistenceSubscriber($this->bus);
    }

    public function testPostPersistDispatchesSyncMessage(): void
    {
        $entity = $this->createMock(EntityInterface::class);
        $entity->method('getId')->willReturn(42);

        $args = new LifecycleEventArgs($entity, $this->objectManager);

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new SyncMessage(get_class($entity), 42)));

        $this->subscriber->postPersist($args);
    }

    public function testPostUpdateDispatchesSyncMessage(): void
    {
        $entity = $this->createMock(EntityInterface::class);
        $entity->method('getId')->willReturn(7);

        $args = new LifecycleEventArgs($entity, $this->objectManager);

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new SyncMessage(get_class($entity), 7)));

        $this->subscriber->postUpdate($args);
    }

    public function testPreRemoveDispatchesDeleteMessage(): void
    {
        $entity = $this->createMock(EntityInterface::class);
        $entity->method('getId')->willReturn(99);

        $args = new LifecycleEventArgs($entity, $this->objectManager);

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new DeleteMessage(get_class($entity), 99)));

        $this->subscriber->preRemove($args);
    }

    public function testIgnoresNonEntityInterfaceObjects(): void
    {
        $nonEntity = new House();
        $args = new LifecycleEventArgs($nonEntity, $this->objectManager);

        $this->bus->expects($this->never())->method('dispatch');

        $this->subscriber->postPersist($args);
        $this->subscriber->postUpdate($args);
        $this->subscriber->preRemove($args);
    }
}
