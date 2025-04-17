<?php

namespace App\Application\Character\Command;

use App\Application\Character\Dto\CharacterInputDto;

final class CreateCharacterCommand
{
    public function __construct(
        public readonly CharacterInputDto $inputDto,
    ) {
    }
}
