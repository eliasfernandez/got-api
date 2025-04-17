<?php

namespace App\Domain\Shared;

interface EntityInterface
{
    public function getId(): ?int;

    public function getUpdatedAt(): ?\DateTimeImmutable;

    public function setUpdatedAt(): void;
}
