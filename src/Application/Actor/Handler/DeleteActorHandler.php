<?php

namespace App\Application\Actor\Handler;

use App\Application\Actor\Command\DeleteActorCommand;
use App\Domain\Actor\Exception\ActorNotFoundException;
use App\Domain\Actor\Repository\ActorRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeleteActorHandler
{
    public function __construct(
        private ActorRepositoryInterface $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(DeleteActorCommand $command): void
    {
        $character = $this->repository->getById($command->id);

        if (!$character) {
            throw new ActorNotFoundException($command->id);
        }

        $this->entityManager->remove($character);
        $this->entityManager->flush();
    }
}
