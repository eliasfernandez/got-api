<?php

namespace App\Application\Character\Handler;

use App\Application\Character\Dto\CharacterOutputDto;
use App\Application\Character\Factory\CharacterOutputFactory;
use App\Application\Character\Query\SearchCharactersQuery;
use App\Domain\Character\Entity\Character;
use App\Domain\Character\Repository\CharacterRepositoryInterface;
use App\Infrastructure\Persistence\Elasticsearch\QueryBuilder;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SearchCharactersHandler
{
    public function __construct(
        private CharacterRepositoryInterface $repository,
        private CharacterOutputFactory $outputFactory,
        private QueryBuilder $elasticsearch,
        private string $elasticIndex,
    ) {
    }

    /**
     * @return array{total:int, result:CharacterOutputDto[]}
     */
    public function __invoke(SearchCharactersQuery $query): array
    {
        $results = $this->elasticsearch
            ->overIndexPattern($this->elasticIndex)
            ->filterByTerm('type', 'character')
            ->from(($query->page - 1) * $query->limit)
            ->limit($query->limit)
            ->search(
                $query->query,
                [
                    'entity.name^5',
                    'entity.nickname^4',
                    'entity.actors.name^3',
                    'entity.houses.name^2',
                    'entity.eventName^1',
                ]
            )->getResults();

        $total = $results['hits']['total']['value'];

        $result = $this->repository->getAllBy(
            [
                'id' => array_map(
                    fn (array $hit) => $hit['_source']['entity']['id'],
                    $results['hits']['hits']
                ),
            ]
        );

        return [
            'total' => $total,
            'result' => array_map(
                fn (Character $character) => $this->outputFactory->fromEntity($character),
                $result
            ),
        ];
    }
}
