<?php

namespace App\Dto\Input;

class ActorInputDto implements InputDtoInterface
{
    public function __construct(
        public string $name,
        public string $character,
        public ?string $link = null,
        public ?array $seasons = null
    ) {
    }
}