<?php

namespace App\Domain\Actor\Exception;

final class ActorAlreadyExistsException extends \RuntimeException
{
    public function __construct(string $name, string $link)
    {
        parent::__construct("Actor with name {$name} or link {$link} already exists.");
    }
}
