<?php

namespace App\Application\Character\Handler;

use App\Application\Character\Command\UpdateCharacterCommand;
use App\Application\Character\Dto\CharacterOutputDto;
use App\Application\Character\Factory\CharacterHydrator;
use App\Application\Character\Factory\CharacterOutputFactory;
use App\Domain\Character\Exception\CharacterAlreadyExistsException;
use App\Domain\Character\Exception\CharacterNotFoundException;
use App\Domain\Character\Repository\CharacterRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateCharacterHandler
{
    public function __construct(
        private CharacterRepositoryInterface $repository,
        private CharacterHydrator $factory,
        private CharacterOutputFactory $outputFactory,
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(UpdateCharacterCommand $command): CharacterOutputDto
    {
        $this->checkUniqueness($command->inputDto->name, $command->inputDto->link, $command->id);

        $character = $this->repository->getById($command->id);

        if (!$character) {
            throw new CharacterNotFoundException($command->id);
        }

        $character = $this->factory->fromDto($command->inputDto, $character);
        $this->em->persist($character);
        $this->em->flush();

        return $this->outputFactory->fromEntity($character);
    }

    private function checkUniqueness(?string $name, ?string $link, int $id): void
    {
        if ($this->repository->findOneByNameOrLink($name, $link, [$id])) {
            throw new CharacterAlreadyExistsException($name, $link);
        }
    }
}
