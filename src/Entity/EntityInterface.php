<?php

namespace App\Entity;

interface EntityInterface
{
    public function getId(): ?int;
    public function getUpdatedAt(): ?\DateTimeImmutable;
    public function setUpdatedAt(): void;
}