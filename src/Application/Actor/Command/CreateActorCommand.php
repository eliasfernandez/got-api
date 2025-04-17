<?php

namespace App\Application\Actor\Command;

use App\Application\Actor\Dto\ActorInputDto;

final class CreateActorCommand
{
    public function __construct(
        public readonly ActorInputDto $inputDto,
    ) {
    }
}
