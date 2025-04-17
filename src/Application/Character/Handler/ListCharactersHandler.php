<?php

namespace App\Application\Character\Handler;

use App\Application\Character\Dto\CharacterOutputDto;
use App\Application\Character\Factory\CharacterOutputFactory;
use App\Application\Character\Query\ListCharactersQuery;
use App\Domain\Character\Entity\Character;
use App\Domain\Character\Repository\CharacterRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ListCharactersHandler
{
    public function __construct(
        private CharacterRepositoryInterface $repository,
        private CharacterOutputFactory $outputFactory,
    ) {
    }

    /**
     * @return array{total:int, result:CharacterOutputDto[]}
     */
    public function __invoke(ListCharactersQuery $query): array
    {
        $paginator = $this->repository->findAllPaginated($query->page, $query->limit);

        return [
            'total' => count($paginator),
            'result' => array_map(
                fn (Character $character) => $this->outputFactory->fromEntity($character),
                iterator_to_array($paginator->getIterator())
            ),
        ];
    }
}
