<?php

namespace App\Message;

class SyncMessage implements SerializableMessage
{
    /**
     * @param class-string $class
     */
    public function __construct(private string $class, private int $id)
    {
    }

    /**
     * @return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }


    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string[]
     */
    public function serialize(): array
    {
        return [
            $this->class,
            $this->id,
        ];
    }
}