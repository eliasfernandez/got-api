<?php

namespace App\Application\Actor\Dto;

use App\Application\Shared\Dto\OutputDtoInterface;

class ActorOutputDto implements OutputDtoInterface
{
    public function __construct(
        public string $uri,
        public string $name,
        public ?string $character,
        public ?string $link = null,
        public ?array $seasons = null,
    ) {
    }
}
