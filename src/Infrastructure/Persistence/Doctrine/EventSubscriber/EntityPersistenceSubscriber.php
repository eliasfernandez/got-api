<?php

namespace App\Infrastructure\Persistence\Doctrine\EventSubscriber;

use App\Domain\Shared\EntityInterface;
use App\Infrastructure\Messenger\Message\DeleteMessage;
use App\Infrastructure\Messenger\Message\SyncMessage;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
class EntityPersistenceSubscriber
{
    public function __construct(private readonly MessageBusInterface $messages)
    {
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof EntityInterface) {
            return;
        }

        $this->messages->dispatch(
            new DeleteMessage($object::class, $object->getId())
        );
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof EntityInterface) {
            return;
        }

        $this->messages->dispatch(
            new SyncMessage($object::class, $object->getId())
        );
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof EntityInterface) {
            return;
        }

        $this->messages->dispatch(
            new SyncMessage($object::class, $object->getId())
        );
    }
}
