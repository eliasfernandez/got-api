<?php

namespace App\Application\Character\Handler;

use App\Application\Character\Command\CreateCharacterCommand;
use App\Application\Character\Dto\CharacterOutputDto;
use App\Application\Character\Factory\CharacterHydrator;
use App\Application\Character\Factory\CharacterOutputFactory;
use App\Domain\Character\Exception\CharacterAlreadyExistsException;
use App\Domain\Character\Repository\CharacterRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateCharacterHandler
{
    public function __construct(
        private CharacterRepositoryInterface $repository,
        private CharacterHydrator $factory,
        private CharacterOutputFactory $outputFactory,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CreateCharacterCommand $command): CharacterOutputDto
    {
        $this->checkUniqueness($command->inputDto->name, $command->inputDto->link);

        $character = $this->factory->fromDto($command->inputDto);
        $this->entityManager->persist($character);
        $this->entityManager->flush();

        return $this->outputFactory->fromEntity($character);
    }

    private function checkUniqueness(?string $name, ?string $link): void
    {
        if ($this->repository->findOneByNameOrLink($name, $link)) {
            throw new CharacterAlreadyExistsException($name, $link);
        }
    }
}
