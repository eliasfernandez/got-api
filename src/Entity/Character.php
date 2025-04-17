<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Character implements EntityInterface
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Actor>
     */
    #[ORM\OneToMany(targetEntity: Actor::class, mappedBy: 'character', orphanRemoval: false)]
    #[Groups('es:character')]
    private Collection $actors;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'parents')]
    #[ORM\JoinTable(name: 'character_parent')]
    private Collection $parentOf;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'parentOf')]
    private Collection $parents;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'siblings')]
    #[ORM\JoinTable(name: 'character_sibling')]
    private Collection $sibling;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'sibling')]
    private Collection $siblings;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'killed')]
    #[ORM\JoinTable(name: 'character_killed')]
    private Collection $killedBy;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'killedBy')]
    private Collection $killed;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'abducted')]
    #[ORM\JoinTable(name: 'character_abducted')]
    private Collection $abductedBy;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'abductedBy')]
    private Collection $abducted;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'serves')]
    #[ORM\JoinTable(name: 'character_serves')]
    private Collection $servedBy;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'servedBy')]
    private Collection $serves;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'guardianOf')]
    #[ORM\JoinTable(name: 'character_guardian')]
    private Collection $guardedBy;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'guardedBy')]
    private Collection $guardianOf;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'marries')]
    #[ORM\JoinTable(name: 'character_marry')]
    private Collection $marriedEngaged;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'marriedEngaged')]
    private Collection $marries;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'allies')]
    private ?self $alliedTo = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'alliedTo')]
    private Collection $allies;

    #[ORM\ManyToMany(targetEntity: House::class, inversedBy: 'characters')]
    #[Groups('es:character')]
    private Collection $houses;

    public function __construct(
        #[ORM\Column(length: 255, unique: true)]
        #[Groups('es:character')]
        private string $name,
        #[ORM\Column(length: 255, unique: true)]
        #[Groups('es:character')]
        private string $link,
        #[ORM\Column]
        #[Groups('es:character')]
        private bool $royal = false,
        #[ORM\Column(length: 255, nullable: true)]
        #[Groups('es:character')]
        private ?string $nickname = null,
        #[ORM\Column]
        #[Groups('es:character')]
        private bool $kingsguard = false,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $thumbnail = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $image = null
    ) {
        $this->actors = new ArrayCollection();
        $this->parentOf = new ArrayCollection();
        $this->parents = new ArrayCollection();
        $this->sibling = new ArrayCollection();
        $this->siblings = new ArrayCollection();
        $this->killedBy = new ArrayCollection();
        $this->killed = new ArrayCollection();
        $this->abductedBy = new ArrayCollection();
        $this->abducted = new ArrayCollection();
        $this->servedBy = new ArrayCollection();
        $this->serves = new ArrayCollection();
        $this->guardedBy = new ArrayCollection();
        $this->guardianOf = new ArrayCollection();
        $this->marriedEngaged = new ArrayCollection();
        $this->marries = new ArrayCollection();
        $this->allies = new ArrayCollection();
        $this->houses = new ArrayCollection();
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

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function isRoyal(): bool
    {
        return $this->royal;
    }

    public function setRoyal(bool $royal): static
    {
        $this->royal = $royal;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function isKingsguard(): bool
    {
        return $this->kingsguard;
    }

    public function setKingsguard(bool $kingsguard): static
    {
        $this->kingsguard = $kingsguard;

        return $this;
    }

    /**
     * @return Collection<int, Actor>
     */
    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function addActor(Actor $actor): static
    {
        if (!$this->actors->contains($actor)) {
            $this->actors->add($actor);
            $actor->setCharacter($this);
        }

        return $this;
    }

    public function removeActor(Actor $actor): static
    {
        if ($this->actors->removeElement($actor)) {
            // set the owning side to null (unless already changed)
            if ($actor->getCharacter() === $this) {
                $actor->setCharacter(null);
            }
        }

        return $this;
    }

    public function emptyActors(): static
    {
        foreach ($this->actors as $actor) {
            $this->removeActor($actor);
        }

        return $this;
    }

    public function getParentOf(): Collection
    {
        return $this->parentOf;
    }

    public function addParentOf(self $parentOf): static
    {
        if (!$this->parentOf->contains($parentOf)) {
            $this->parentOf->add($parentOf);
        }

        return $this;
    }

    public function removeParentOf(self $parentOf): static
    {
        $this->parentOf->removeElement($parentOf);

        return $this;
    }

    public function getParents(): Collection
    {
        return $this->parents;
    }

    public function addParent(self $parent): static
    {
        if (!$this->parents->contains($parent)) {
            $this->parents->add($parent);
            $parent->addParentOf($this);
        }

        return $this;
    }

    public function removeParent(self $parent): static
    {
        if ($this->parents->removeElement($parent)) {
            $parent->removeParentOf($this);
        }

        return $this;
    }

    public function getSibling(): Collection
    {
        return $this->sibling;
    }

    public function addSibling(self $sibling): static
    {
        if (!$this->sibling->contains($sibling)) {
            $this->sibling->add($sibling);
        }

        return $this;
    }

    public function removeSibling(self $sibling): static
    {
        $this->sibling->removeElement($sibling);

        return $this;
    }

    public function getSiblings(): Collection
    {
        return $this->siblings;
    }

    public function getKilledBy(): Collection
    {
        return $this->killedBy;
    }

    public function addKilledBy(self $killedBy): static
    {
        if (!$this->killedBy->contains($killedBy)) {
            $this->killedBy->add($killedBy);
        }

        return $this;
    }

    public function removeKilledBy(self $killedBy): static
    {
        $this->killedBy->removeElement($killedBy);

        return $this;
    }

    public function getKilled(): Collection
    {
        return $this->killed;
    }

    public function addKilled(self $killed): static
    {
        if (!$this->killed->contains($killed)) {
            $this->killed->add($killed);
            $killed->addKilledBy($this);
        }

        return $this;
    }

    public function removeKilled(self $killed): static
    {
        if ($this->killed->removeElement($killed)) {
            $killed->removeKilledBy($this);
        }

        return $this;
    }

    public function getAbductedBy(): Collection
    {
        return $this->abductedBy;
    }

    public function addAbductedBy(self $abductedBy): static
    {
        if (!$this->abductedBy->contains($abductedBy)) {
            $this->abductedBy->add($abductedBy);
        }

        return $this;
    }

    public function removeAbductedBy(self $abductedBy): static
    {
        $this->abductedBy->removeElement($abductedBy);

        return $this;
    }

    public function getAbducted(): Collection
    {
        return $this->abducted;
    }

    public function addAbducted(self $abducted): static
    {
        if (!$this->abducted->contains($abducted)) {
            $this->abducted->add($abducted);
            $abducted->addAbductedBy($this);
        }

        return $this;
    }

    public function removeAbducted(self $abducted): static
    {
        if ($this->abducted->removeElement($abducted)) {
            $abducted->removeAbductedBy($this);
        }

        return $this;
    }

    public function getServedBy(): Collection
    {
        return $this->servedBy;
    }

    public function addServedBy(self $servedBy): static
    {
        if (!$this->servedBy->contains($servedBy)) {
            $this->servedBy->add($servedBy);
        }

        return $this;
    }

    public function removeServedBy(self $servedBy): static
    {
        $this->servedBy->removeElement($servedBy);

        return $this;
    }

    public function getServes(): Collection
    {
        return $this->serves;
    }

    public function addSerf(self $serf): static
    {
        if (!$this->serves->contains($serf)) {
            $this->serves->add($serf);
            $serf->addServedBy($this);
        }

        return $this;
    }

    public function removeSerf(self $serf): static
    {
        if ($this->serves->removeElement($serf)) {
            $serf->removeServedBy($this);
        }

        return $this;
    }

    public function getGuardedBy(): Collection
    {
        return $this->guardedBy;
    }

    public function addGuardedBy(self $guardedBy): static
    {
        if (!$this->guardedBy->contains($guardedBy)) {
            $this->guardedBy->add($guardedBy);
        }

        return $this;
    }

    public function removeGuardedBy(self $guardedBy): static
    {
        $this->guardedBy->removeElement($guardedBy);

        return $this;
    }

    public function getGuardianOf(): Collection
    {
        return $this->guardianOf;
    }

    public function addGuardianOf(self $guardianOf): static
    {
        if (!$this->guardianOf->contains($guardianOf)) {
            $this->guardianOf->add($guardianOf);
            $guardianOf->addGuardedBy($this);
        }

        return $this;
    }

    public function removeGuardianOf(self $guardianOf): static
    {
        if ($this->guardianOf->removeElement($guardianOf)) {
            $guardianOf->removeGuardedBy($this);
        }

        return $this;
    }

    public function getMarriedEngaged(): Collection
    {
        return $this->marriedEngaged;
    }

    public function addMarriedEngaged(self $marriedEngaged): static
    {
        if (!$this->marriedEngaged->contains($marriedEngaged)) {
            $this->marriedEngaged->add($marriedEngaged);
        }

        return $this;
    }

    public function removeMarriedEngaged(self $marriedEngaged): static
    {
        $this->marriedEngaged->removeElement($marriedEngaged);

        return $this;
    }

    public function getMarries(): Collection
    {
        return $this->marries;
    }

    public function addMarry(self $marry): static
    {
        if (!$this->marries->contains($marry)) {
            $this->marries->add($marry);
            $marry->addMarriedEngaged($this);
        }

        return $this;
    }

    public function removeMarry(self $marry): static
    {
        if ($this->marries->removeElement($marry)) {
            $marry->removeMarriedEngaged($this);
        }

        return $this;
    }

    public function getAlliedTo(): ?self
    {
        return $this->alliedTo;
    }

    public function setAlliedTo(?self $alliedTo): static
    {
        $this->alliedTo = $alliedTo;

        return $this;
    }

    public function getAllies(): Collection
    {
        return $this->allies;
    }

    public function addAlly(self $ally): static
    {
        if (!$this->allies->contains($ally)) {
            $this->allies->add($ally);
            $ally->setAlliedTo($this);
        }

        return $this;
    }

    public function removeAlly(self $ally): static
    {
        if ($this->allies->removeElement($ally)) {
            // set the owning side to null (unless already changed)
            if ($ally->getAlliedTo() === $this) {
                $ally->setAlliedTo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, House>
     */
    public function getHouses(): Collection
    {
        return $this->houses;
    }

    public function addHouse(House $house): static
    {
        if (!$this->houses->contains($house)) {
            $this->houses->add($house);
        }

        return $this;
    }

    public function removeHouse(House $house): static
    {
        $this->houses->removeElement($house);

        return $this;
    }

    public function emptyHouses(): static
    {
        foreach ($this->houses as $house) {
            $this->removeHouse($house);
        }

        return $this;
    }
}
