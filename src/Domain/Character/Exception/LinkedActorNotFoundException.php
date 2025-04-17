<?php

namespace App\Domain\Character\Exception;

final class LinkedActorNotFoundException extends \RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Actor with ID {$id} not found.");
    }
}
