<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Shared\EntityInterface;
use Doctrine\ORM\EntityManagerInterface;

trait IdOrderedTrait
{
    /**
     * @return EntityManagerInterface
     */
    abstract protected function getEntityManager();

    /***
     * Returns a list of hydrated object in the same order of $ids array
     *
     * @param  string[] $ids
     * @return array<EntityInterface>
     */
    public function findByIdOrdered(array $ids): array
    {
        $qb = $this->createQueryBuilder('qb');
        $qb->andWhere('qb.id IN(:ids)')
            ->setParameter('ids', $ids);

        $unOrdered = $qb->getQuery()->getResult();
        $ordered = [];
        foreach ($ids as $id) {
            foreach ($unOrdered as $entity) {
                if ($entity->getId() === $id) {
                    $ordered[] = $entity;
                }
            }
        }

        return $ordered;
    }
}
