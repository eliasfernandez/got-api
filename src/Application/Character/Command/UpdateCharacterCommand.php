<?php

namespace App\Application\Character\Command;

use App\Application\Character\Dto\CharacterInputDto;

final class UpdateCharacterCommand
{
    public function __construct(
        public readonly int $id,
        public readonly CharacterInputDto $inputDto,
    ) {
    }
}
