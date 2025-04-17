<?php

namespace App\Application\Character\Command;

final class DeleteCharacterCommand
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}
