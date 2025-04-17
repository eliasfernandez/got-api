<?php

namespace App\Domain\Character\Repository;

use App\Domain\Character\Entity\Character;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface CharacterRepositoryInterface
{
    public function getById(int $id): ?Character;

    public function getAllBy(array $criteria = []): array;

    public function findAllPaginated(int $page, int $limit): Paginator;

    public function findOneByNameOrLink(string $name, string $link, array $ids = []): ?object;
}
