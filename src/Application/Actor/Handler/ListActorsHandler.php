<?php

namespace App\Application\Actor\Handler;

use App\Application\Actor\Factory\ActorOutputFactory;
use App\Application\Actor\Query\ListActorsQuery;
use App\Domain\Actor\Entity\Actor;
use App\Domain\Actor\Repository\ActorRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ListActorsHandler
{
    public function __construct(
        private ActorRepositoryInterface $repository,
        private ActorOutputFactory $outputFactory,
    ) {
    }

    public function __invoke(ListActorsQuery $query): array
    {
        $paginator = $this->repository->findAllPaginated($query->page, $query->limit);

        return [
            'total' => count($paginator),
            'result' => array_map(
                fn (Actor $actor) => $this->outputFactory->fromEntity($actor),
                iterator_to_array($paginator->getIterator())
            ),
        ];
    }
}
