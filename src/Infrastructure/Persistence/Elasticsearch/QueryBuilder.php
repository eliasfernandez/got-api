<?php

namespace App\Infrastructure\Persistence\Elasticsearch;

use Elastic\Elasticsearch\Client;

class QueryBuilder
{
    private array $indices;

    private array $query;

    private ?array $aggregations;

    private ?array $boolFilter;

    private int $limit;

    private int $from;

    private ?array $results;

    private array $sort;

    private array $filters;

    private array $pointInTime;

    private array $searchAfter;

    public function __construct(private readonly Client $client)
    {
        $this->query = [
            'bool' => [
                'must' => [],
                'filter' => [],
                'should' => [],
                'must_not' => [],
            ],
        ];
        $this->boolFilter = null;
        $this->filters = [];
        $this->aggregations = [];
        $this->results = null;
        $this->limit = 20;
        $this->from = 0;
        $this->sort = [];
        $this->pointInTime = [];
        $this->searchAfter = [];
    }

    public function overIndexPattern(string $pattern): self
    {
        $this->indices = [];
        $this->indices[] = $pattern;

        return $this;
    }

    public function from(int $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function sort(array $config): self
    {
        $this->sort = $config;

        return $this;
    }

    public function filterByTerm(string $field, ...$values): self
    {
        if (!$this->boolFilter) {
            $this->boolFilter = ['bool' => ['must' => []]];
        }

        if (1 === count($values)) {
            $this->boolFilter['bool']['must'][] = [
                'term' => [$field => $values[0]],
            ];

            return $this;
        }

        $this->boolFilter['bool']['must'][] = [
            'terms' => [$field => $values],
        ];

        return $this;
    }

    public function search($search, array $fields): self
    {
        $type = '*' === $search ? 'should' : 'must';

        if (!$this->query['bool'][$type]) {
            $this->query['bool'][$type] = [];
        }

        $this->query['bool'][$type][] = [
            'multi_match' => array_filter(
                [
                    'type' => 'bool_prefix',
                    'query' => $search,
                    'fields' => $fields,
                ]
            ),
        ];

        return $this;
    }

    public function addRange(string $field, array $config): self
    {
        $this->query['bool']['minimum_should_match'] = 1;
        $this->query['bool']['should'][] = [
            'range' => [$field => $config],
        ];

        return $this;
    }

    public function createQuery(): array
    {
        if (empty($this->indices) && [] !== $this->searchAfter) {
            throw new \RuntimeException('You MUST specify an index to query over, unless using a Point In Time.');
        }

        if ($this->boolFilter) {
            $this->query['bool']['filter'][] = $this->boolFilter;
        }

        foreach ($this->filters as $filter) {
            $this->query['bool']['filter'][] = $filter;
        }

        $baseQuery = [
            'index' => implode(',', $this->indices),
            'body' => array_filter(
                [
                    'aggs' => $this->aggregations,
                    'size' => $this->limit,
                    'from' => $this->from,
                    'sort' => $this->sort,
                    'query' => $this->query,
                    'pit' => $this->pointInTime,
                ],
                fn ($x): bool => null !== $x && [] !== $x
            ),
        ];

        // Use alternate pagination for paging through large datasets
        if ([] !== $this->pointInTime) {
            unset($baseQuery['body']['from'], $baseQuery['index']);
            if ($this->searchAfter) {
                $baseQuery['body']['search_after'] = $this->searchAfter;
            }
        }

        return $baseQuery;
    }

    public function getResults(bool $cached = true): array
    {
        if (null !== $this->results && $cached) {
            return $this->results;
        }

        $search = $this->createQuery();
        $results = $this->client->search($search);
        $this->results = json_decode(
            $results->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $this->results;
    }

    public function resetResults(): void
    {
        $this->results = null;
    }
}
