<?php

namespace App\Application\Character\Query;

class SearchCharactersQuery
{
    public function __construct(
        public readonly int $page,
        public readonly int $limit,
        public readonly string $query,
    ) {
    }
}
