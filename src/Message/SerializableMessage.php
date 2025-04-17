<?php

namespace App\Message;

interface SerializableMessage
{
    /**
     * Return a deduplication ID suitable for this message
     */
    public function getId(): string|int;

    /**
     * Return an array which can be passed to the constructor with variadics (...)
     */
    public function serialize(): array;
}
