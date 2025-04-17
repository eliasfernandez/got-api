<?php

namespace App\Dto\Output;

class CharacterOutputDto implements OutputDtoInterface
{
    /**
     * @param string[] $houses
     */
    public function __construct(
        public string $uri,
        public string $name,
        public array $actors = [],
        public ?string $link = null,
        public bool $royal = false,
        public ?string $nickname = null,
        public bool $kingsguard = false,
        public ?string $thumbnail = null,
        public ?string $image = null,
        public array $houses = []
    ) {
    }
}