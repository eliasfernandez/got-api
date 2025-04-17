<?php

namespace App\Application\Actor\Handler;

use App\Application\Actor\Dto\ActorOutputDto;
use App\Application\Actor\Factory\ActorOutputFactory;
use App\Application\Actor\Query\GetActorByIdQuery;
use App\Domain\Actor\Exception\ActorNotFoundException;
use App\Domain\Actor\Repository\ActorRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetActorByIdHandler
{
    public function __construct(
        private ActorRepositoryInterface $repository,
        private ActorOutputFactory $factory,
    ) {
    }

    public function __invoke(GetActorByIdQuery $query): ActorOutputDto
    {
        $result = $this->repository->getById($query->id);

        if (null === $result) {
            throw new ActorNotFoundException($query->id);
        }

        return $this->factory->fromEntity($result);
    }
}
