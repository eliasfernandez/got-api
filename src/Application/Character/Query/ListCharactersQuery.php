<?php

namespace App\Application\Character\Query;

class ListCharactersQuery
{
    public function __construct(
        public readonly int $page,
        public readonly int $limit,
    ) {
    }
}
