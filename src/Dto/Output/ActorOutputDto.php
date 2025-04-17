<?php

namespace App\Dto\Output;

class ActorOutputDto implements OutputDtoInterface
{
    public function __construct(
        public string $uri,
        public string $name,
        public ?string $character,
        public ?string $link = null,
        public ?array $seasons = null
    ) {
    }
}