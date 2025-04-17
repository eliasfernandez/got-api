<?php

namespace App\Application\Character\Dto;

use App\Application\Shared\Dto\InputDtoInterface;

class CharacterInputDto implements InputDtoInterface
{
    public function __construct(
        public string $name,
        /**
         * @var string[]
         */
        public array $actors = [],
        public ?string $link = null,
        public bool $royal = false,
        public ?string $nickname = null,
        public bool $kingsguard = false,
        public ?string $thumbnail = null,
        public ?string $image = null,
        public array $houses = [],
    ) {
    }
}
