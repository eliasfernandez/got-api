<?php

namespace App\Domain\Actor\Exception;

final class ActorNotFoundException extends \RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Actor with ID {$id} not found.");
    }
}
