<?php

namespace App\Application\Character\Handler;

use App\Application\Character\Dto\CharacterOutputDto;
use App\Application\Character\Factory\CharacterOutputFactory;
use App\Application\Character\Query\GetCharacterByIdQuery;
use App\Domain\Character\Exception\CharacterNotFoundException;
use App\Domain\Character\Repository\CharacterRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetCharacterByIdHandler
{
    public function __construct(
        private CharacterRepositoryInterface $repository,
        private CharacterOutputFactory $factory,
    ) {
    }

    public function __invoke(GetCharacterByIdQuery $query): CharacterOutputDto
    {
        $result = $this->repository->getById($query->id);

        if (null === $result) {
            throw new CharacterNotFoundException($query->id);
        }

        return $this->factory->fromEntity($result);
    }
}
