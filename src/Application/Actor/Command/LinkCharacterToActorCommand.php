<?php

namespace App\Application\Actor\Command;

class LinkCharacterToActorCommand
{
    public function __construct(
        public readonly int $id,
        public readonly array $characterUris = [],
    ) {
    }
}
