<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

trait PaginatedRepositoryTrait
{
    protected const DEFAULT_SORT = 'name';

    public function findAllPaginated(int $page, int $size): Paginator
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy(sprintf('a.%s', self::DEFAULT_SORT), 'ASC')
            ->setFirstResult(($page - 1) * $size)
            ->setMaxResults($size);

        return new Paginator($query);
    }

    abstract public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder;
}
