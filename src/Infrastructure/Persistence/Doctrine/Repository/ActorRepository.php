<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Actor\Entity\Actor;
use App\Domain\Actor\Repository\ActorRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActorRepository extends ServiceEntityRepository implements ActorRepositoryInterface
{
    use PaginatedRepositoryTrait;
    use ByNameOrLinkTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Actor::class);
    }

    public function getById(int $id): ?Actor
    {
        return $this->find($id);
    }
}
