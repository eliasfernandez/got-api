<?php

namespace App\Application\Actor\Command;

final class DeleteActorCommand
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}
