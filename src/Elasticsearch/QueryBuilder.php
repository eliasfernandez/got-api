<?php

namespace App\Elasticsearch;

use Elastic\Elasticsearch\ClientInterface;
use Elasticsearch\Client;

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

    /**
     * @param Client $client
     */
    public function __construct(private readonly ClientInterface $client) {
        $this->query = [
            'bool' => [
                'must' => [],
                'filter' => [],
                'should' => [],
                'must_not' => []
            ]
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

    /**
     * @param mixed[] $config
     */
    public function sort(array $config): self
    {
        $this->sort = $config;
        return $this;
    }

    public function filterByExists(string $field): self
    {
        if (!$this->boolFilter) {
            $this->boolFilter = ['bool' => ['must' => []]];
        }
        $this->boolFilter['bool']['must'][] = [
            'exists' => ['field' => $field]
        ];
        return $this;
    }

    public function filterByTerm(string $field, ...$values): self
    {
        if (!$this->boolFilter) {
            $this->boolFilter = ['bool' => ['must' => []]];
        }
        if (1 === count($values)) {
            $this->boolFilter['bool']['must'][] = [
                'term' => [$field => $values[0]]
            ];
            return $this;
        }
        $this->boolFilter['bool']['must'][] = [
            'terms' => [$field => $values]
        ];
        return $this;
    }

    public function searchByTerm(string $field, ...$values): self
    {
        if (!isset($this->query['bool']['should'])) {
            $this->query['bool']['should'] = [];
        }
        if (1 === count($values)) {
            $this->query['bool']['should'][] = [
                'term' => [$field => $values[0]]
            ];
            return $this;
        }
        $this->query['bool']['should'][] = [
            'terms' => [$field => $values]
        ];
        return $this;
    }

    public function filterOutByTerm(string $field, ...$values): self
    {
        if (!$this->boolFilter) {
            $this->boolFilter = ['bool' => ['must_not' => []]];
        }
        if (1 === count($values)) {
            $this->boolFilter['bool']['must_not'][] = [
                'term' => [$field => $values[0]]
            ];
            return $this;
        }
        $this->boolFilter['bool']['must_not'][] = [
            'terms' => [$field => $values]
        ];
        return $this;
    }

    public function filterByMatch(string $field, $value): self
    {
        if (!$this->boolFilter) {
            $this->boolFilter = ['bool' => ['must' => []]];
        }
        $this->boolFilter['bool']['must'][] = [
            'match' => [$field => $value]
        ];
        return $this;
    }

    public function filterOutByMatch(string $field, $value): self
    {
        if (!$this->boolFilter) {
            $this->boolFilter = ['bool' => ['must_not' => []]];
        }
        $this->boolFilter['bool']['must_not'][] = [
            'match' => [$field => $value]
        ];
        return $this;
    }

    public function filterOutExists(string $field): self
    {
        if (!$this->boolFilter) {
            $this->boolFilter = ['bool' => ['must_not' => []]];
        }
        $this->boolFilter['bool']['must_not'][] = [
            'exists' => ['field' => $field]
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
            'multi_match' => array_filter([
                'type' => 'bool_prefix',
                'query' => $search,
                'fields' => $fields
            ])
        ];
        return $this;
    }

    public function filterByRange(string $field, array $config): self
    {
        $this->filters[] = [
            'range' => [
                $field => $config
            ]
        ];
        return $this;
    }

    public function filterByDateRange(\DatePeriod $range, string $field = '@timestamp'): self
    {
        $this->filterByRange($field, [
            'gte' => $range->getStartDate()->format('c'),
            'lte' => $range->getEndDate()->format('c'),
            'format' => 'strict_date_optional_time'
        ]);
        return $this;
    }

    public function setMinimumMatches(int $match = 1): self
    {
        $this->query['bool']['minimum_should_match'] = $match;
        return $this;
    }

    public function addRange(string $field, array $config): self
    {
        $this->query['bool']['minimum_should_match'] = 1;
        $this->query['bool']['should'][] = [
            'range' => [
                $field => $config
            ]
        ];
        return $this;
    }

    public function addAggregation(string $name, array $config): self
    {
        $this->aggregations[$name] = $config;
        return $this;
    }

    public function usePointInTime(string $id, string $keepAlive = '1m'): self
    {
        $this->pointInTime = [
            'id' => $id,
            // Extend the keep alive by 1 minute
            'keep_alive' => $keepAlive,
        ];
        return $this;
    }

    /**
     * @param mixed[] $sort
     */
    public function setSearchAfter(array $sort): self
    {
        $this->searchAfter = $sort;
        return $this;
    }

    /**
     * @return array<string, mixed[]>
     */
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
            'body' => array_filter([
                'aggs' => $this->aggregations,
                'size' => $this->limit,
                'from' => $this->from,
                'sort' => $this->sort,
                'query' => $this->query,
                'pit' => $this->pointInTime,
            ], fn ($x): bool => $x !== null && $x !== [])
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
