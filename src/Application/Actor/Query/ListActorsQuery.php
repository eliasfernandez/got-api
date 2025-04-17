<?php

namespace App\Application\Actor\Query;

class ListActorsQuery
{
    public function __construct(public readonly int $page, public readonly int $limit)
    {
    }
}
