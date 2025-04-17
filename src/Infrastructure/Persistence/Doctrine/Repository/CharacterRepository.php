<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Character\Entity\Character;
use App\Domain\Character\Repository\CharacterRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CharacterRepository extends ServiceEntityRepository implements CharacterRepositoryInterface
{
    use PaginatedRepositoryTrait;
    use IdOrderedTrait;
    use ByNameOrLinkTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Character::class);
    }

    public function getById(int $id): ?Character
    {
        return $this->find($id);
    }

    public function getAllBy(array $criteria = []): array
    {
        return $this->findBy($criteria);
    }
}
