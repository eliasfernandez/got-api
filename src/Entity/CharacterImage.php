<?php

namespace App\Entity;

use App\Repository\CharacterImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CharacterImageRepository::class)]
final class CharacterImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    public function __construct(
        #[ORM\Column(length: 255)]
        private string $alt,
        #[ORM\Column(length: 255)]
        private string $url,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlt(): string
    {
        return $this->alt;
    }

    public function setAlt(string $alt): static
    {
        $this->alt = $alt;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function __toString(): string
    {
        return $this->url;
    }
}
