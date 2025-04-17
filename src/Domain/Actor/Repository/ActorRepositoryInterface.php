<?php

namespace App\Domain\Actor\Repository;

use App\Domain\Actor\Entity\Actor;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface ActorRepositoryInterface
{
    public function getById(int $id): ?Actor;

    public function findAllPaginated(int $page, int $limit): Paginator;

    public function findOneByNameOrLink(string $name, string $link, array $id = []): ?object;
}
