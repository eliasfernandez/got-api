<?php

namespace App\Domain\Actor\Exception;

final class LinkedCharacterNotFoundException extends \RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Character with ID {$id} not found.");
    }
}
