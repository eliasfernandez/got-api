<?php

namespace App\Application\Character\Handler;

use App\Application\Character\Command\DeleteCharacterCommand;
use App\Domain\Character\Exception\CharacterNotFoundException;
use App\Domain\Character\Repository\CharacterRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeleteCharacterHandler
{
    public function __construct(
        private CharacterRepositoryInterface $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(DeleteCharacterCommand $command): void
    {
        $character = $this->repository->getById($command->id);

        if (!$character) {
            throw new CharacterNotFoundException($command->id);
        }

        $this->entityManager->remove($character);
        $this->entityManager->flush();
    }
}
