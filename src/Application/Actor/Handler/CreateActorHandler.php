<?php

namespace App\Application\Actor\Handler;

use App\Application\Actor\Command\CreateActorCommand;
use App\Application\Actor\Dto\ActorOutputDto;
use App\Application\Actor\Factory\ActorHydrator;
use App\Application\Actor\Factory\ActorOutputFactory;
use App\Domain\Actor\Exception\ActorAlreadyExistsException;
use App\Domain\Actor\Repository\ActorRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateActorHandler
{
    public function __construct(
        private ActorRepositoryInterface $repository,
        private ActorHydrator $factory,
        private ActorOutputFactory $outputFactory,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CreateActorCommand $command): ActorOutputDto
    {
        $this->checkUniqueness($command->inputDto->name, $command->inputDto->link);

        $character = $this->factory->fromDto($command->inputDto);
        $this->entityManager->persist($character);
        $this->entityManager->flush();

        return $this->outputFactory->fromEntity($character);
    }

    private function checkUniqueness(?string $name, ?string $link): void
    {
        if (
            $this->repository->findOneByNameOrLink($name, $link)
        ) {
            throw new ActorAlreadyExistsException($name, $link);
        }
    }
}
