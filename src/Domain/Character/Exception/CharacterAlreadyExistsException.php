<?php

namespace App\Domain\Character\Exception;

final class CharacterAlreadyExistsException extends \RuntimeException
{
    public function __construct(string $name, string $link)
    {
        parent::__construct("Character with name {$name} or link {$link} already exists.");
    }
}
