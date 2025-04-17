<?php

namespace App\Application\Character\Query;

class GetCharacterByIdQuery
{
    public function __construct(public readonly int $id)
    {
    }
}
