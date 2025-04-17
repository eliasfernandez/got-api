<?php

namespace App\Infrastructure\Messenger\Message;

class DeleteMessage implements SerializableMessage
{
    /**
     * @param class-string $class
     */
    public function __construct(private string $class, private int $id)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return array<int, class-string|int>
     */
    public function serialize(): array
    {
        return [
            $this->class,
            $this->id,
        ];
    }
}
