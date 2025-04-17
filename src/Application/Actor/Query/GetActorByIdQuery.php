<?php

namespace App\Application\Actor\Query;

class GetActorByIdQuery
{
    public function __construct(public readonly int $id)
    {
    }
}
