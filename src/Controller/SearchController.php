<?php

namespace App\Controller;

use App\Elasticsearch\QueryBuilder;
use App\Entity\Character;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends ApiController
{
    protected const CONTROLLER_CLASS = Character::class;

    #[Route('/api/search', name: 'app_main', methods: ['GET'])]
    public function search(Request $request, QueryBuilder $elasticsearch, string $elasticIndex): Response
    {
        $limit = $request->query->getInt('limit', self::MAX_RESULTS_PER_PAGE);
        $page = $request->query->getInt('page', self::PAGE_DEFAULT);
        $query = $request->get('q', '*');
        $query = empty($query) ? '*' : $query;

        $results = $elasticsearch->overIndexPattern($elasticIndex)
            ->filterByTerm('type', 'character')
            ->from(($page - 1) * $limit)
            ->limit($limit)
            ->search($query, [
                'entity.name^5',
                'entity.nickname^4',
                'entity.actors.name^3',
                'entity.houses.name^2',
                'entity.eventName^1',
            ])->getResults();

        $total = $results['hits']['total']['value'];

        $result = $this->repository->findBy([
            'id' => array_map(
                fn (array $hit) => $hit['_source']['entity']['id'],
                $results['hits']['hits']
            )
        ]);

        return $this->getPaginatedResponse(
            $result,
            $page,
            $total,
            $limit
        );
    }

}