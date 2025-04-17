<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

trait PaginatedRepositoryTrait
{
    protected const DEFAULT_SORT = 'name';

    /**
     * @return array{int, Paginator}
     */
    public function findAllPaginated(int $page, int $size): array
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy(sprintf('a.%s', self::DEFAULT_SORT), 'ASC')
            ->setFirstResult(($page - 1) * $size)
            ->setMaxResults($size);
        $paginator = new Paginator($query);


        return [count($paginator), $paginator];
    }

    public abstract function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder;
}