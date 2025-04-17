<?php

namespace App\Application\Character\Command;

class LinkActorsToCharacterCommand
{
    public function __construct(
        public readonly int $id,
        public readonly array $actorUris,
    ) {
    }
}
