<?php

namespace App\Application\Actor\Handler;

use App\Application\Actor\Command\UpdateActorCommand;
use App\Application\Actor\Dto\ActorOutputDto;
use App\Application\Actor\Factory\ActorHydrator;
use App\Application\Actor\Factory\ActorOutputFactory;
use App\Domain\Actor\Exception\ActorNotFoundException;
use App\Domain\Actor\Repository\ActorRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateActorHandler
{
    public function __construct(
        private ActorRepositoryInterface $repository,
        private ActorHydrator $factory,
        private ActorOutputFactory $outputFactory,
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(UpdateActorCommand $command): ActorOutputDto
    {
        $character = $this->repository->getById($command->id);

        if (!$character) {
            throw new ActorNotFoundException($command->id);
        }

        $character = $this->factory->fromDto($command->inputDto, $character);
        $this->em->persist($character);
        $this->em->flush();

        return $this->outputFactory->fromEntity($character);
    }
}
