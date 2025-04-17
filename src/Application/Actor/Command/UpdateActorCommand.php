<?php

namespace App\Application\Actor\Command;

use App\Application\Actor\Dto\ActorInputDto;

final class UpdateActorCommand
{
    public function __construct(
        public readonly int $id,
        public readonly ActorInputDto $inputDto,
    ) {
    }
}
