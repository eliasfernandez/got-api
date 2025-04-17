<?php

namespace App\Domain\Actor\Entity;

use App\Domain\Character\Entity\Character;
use App\Domain\Shared\EntityInterface;
use App\Domain\Shared\TimestampTrait;
use App\Infrastructure\Persistence\Doctrine\Repository\ActorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ActorRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Actor implements EntityInterface
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(length: 255)]
        #[Groups(['es:character', 'es:actor'])]
        private string $name,
        #[ORM\Column(length: 255, unique: true)]
        private ?string $link,
        #[ORM\ManyToOne(inversedBy: 'actors')]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Character $character = null,
        #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
        #[Groups('es:actor')]
        private array $seasons = [],
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getSeasons(): array
    {
        return array_map(fn (string $season) => (int) $season, $this->seasons);
    }

    public function setSeasons(array $seasons): static
    {
        $this->seasons = $seasons;

        return $this;
    }

    public function getCharacter(): ?Character
    {
        return $this->character;
    }

    public function setCharacter(?Character $character): static
    {
        $this->character = $character;

        return $this;
    }
}
