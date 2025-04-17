<?php

namespace App\Application\Character\Factory;

use App\Application\Character\Dto\CharacterInputDto;
use App\Domain\Actor\Entity\Actor;
use App\Domain\Character\Entity\Character;
use App\Domain\House\Entity\House;
use Doctrine\ORM\EntityManagerInterface;

final class CharacterHydrator
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function fromDto(CharacterInputDto $dto, ?Character $character = null): Character
    {
        if (!$character) {
            $character = new Character($dto->name, $dto->link);
        }

        $character->setName($dto->name)
            ->setLink($dto->link)
            ->setRoyal($dto->royal)
            ->setNickname($dto->nickname)
            ->setKingsguard($dto->kingsguard)
            ->setThumbnail($dto->thumbnail)
            ->setImage($dto->image);

        $this->setActors($character, $dto->actors);
        $this->setHouses($character, $dto->houses);

        return $character;
    }

    private function setActors(Character $character, array $actorUris): void
    {
        $character->emptyActors();

        foreach ($actorUris as $uri) {
            $id = (int) basename($uri);
            // or use a service to parse this
            $actor = $this->em->find(Actor::class, $id);

            if (!$actor) {
                throw new \InvalidArgumentException("Invalid actor URI: $uri");
            }

            $character->addActor($actor);
        }
    }

    private function setHouses(Character $character, array $houseNames): void
    {
        $character->emptyHouses();

        foreach ($houseNames as $name) {
            $house = $this->em->getRepository(House::class)->findOneBy(['name' => $name]);

            if (!$house) {
                $house = new House($name);
                $this->em->persist($house);
            }

            $character->addHouse($house);
        }
    }
}
