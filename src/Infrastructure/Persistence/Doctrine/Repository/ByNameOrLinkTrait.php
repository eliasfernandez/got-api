<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\QueryBuilder;

trait ByNameOrLinkTrait
{
    public function findOneByNameOrLink(string $name, string $link, array $ids = []): ?object
    {
        $queryBuilder = $this->createQueryBuilder('item')
            ->where(
                'item.name = :name OR item.link = :link'
            )
            ->setParameter('name', $name)
            ->setParameter('link', $link);
        if (count($ids) > 0) {
            $queryBuilder->andWhere('item.id NOT IN (:ids)')
                ->setParameter('ids', $ids);
        }

        return $queryBuilder->getQuery()
            ->getOneOrNullResult();
    }

    abstract public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder;
}
