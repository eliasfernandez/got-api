<?php

namespace App\Domain\Character\Exception;

final class CharacterNotFoundException extends \RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Character with ID {$id} not found.");
    }
}
