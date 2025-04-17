<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Character;
use App\Entity\House;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture implements FixtureInterface
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function load(ObjectManager $manager): void
    {

        $json = json_decode(file_get_contents(__DIR__ . '/Resources/got-characters.json'), true, 512, JSON_THROW_ON_ERROR);
        foreach ($json['characters'] as $characterItem) {
            $character = $this->getOrCreateCharacter($characterItem, $manager);

            if (!empty($characterItem['actorLink'])) {
               $this->getOrCreateActor($character, $characterItem, $manager);
            }

            if (!empty($characterItem['actors']) && is_array($characterItem['actors'])) {
                foreach ($characterItem['actors'] as $actorItem) {
                    $this->getOrCreateActor($character, $actorItem, $manager);
                }
            }

            if (!empty($characterItem['houseName']) && is_array($characterItem['houseName'])) {
                foreach ($characterItem['houseName'] as $houseItem) {
                    $house = $this->getOrCreateHouse($houseItem, $manager);
                    $character->addHouse($house);
                }
            }

            if (!empty($characterItem['houseName']) && is_string($characterItem['houseName'])) {
                $house = $this->getOrCreateHouse($characterItem['houseName'], $manager);
                $character->addHouse($house);
            }

            $manager->flush();
        }
    }

    public function getOrCreateHouse(string $houseItem, ObjectManager $manager): House
    {
        $house = $manager->getRepository(House::class)->findOneBy(['name' => $houseItem]);
        if (null === $house) {
            $house = new House($houseItem);
        }

        $manager->persist($house);

        return $house;
    }

    public function getOrCreateCharacter(mixed $characterItem, ObjectManager $manager): Character
    {
        if (!isset($characterItem['characterLink'])) {
            $characterItem['characterLink'] = sprintf('/character/%s/', $this->slugger->slug(
                strtolower($characterItem['characterName'])
            ));
        }

        $character = $manager->getRepository(Character::class)->findOneBy(['link' => $characterItem['characterLink']]);
        if (null === $character) {
            $character = new Character(
                $characterItem['characterName'],
                $characterItem['characterLink'],
                $characterItem['royal'] ?? false,
                $characterItem['nickname'] ?? null,
                $characterItem['kingsguard'] ?? false,
                $characterItem['characterImageThumb'] ?? null,
                $characterItem['characterImageFull'] ?? null,
            );
        }

        $manager->persist($character);

        return $character;
    }

    public function getOrCreateActor(Character $character, mixed $actorItem, ObjectManager $manager): Actor
    {
        if (!isset($actorItem['actorLink'])) {
            $actorItem['actorLink'] = sprintf('/name/%s/', $this->slugger->slug(
                strtolower($actorItem['actorName'])
            ));
        }

        $actor = $manager->getRepository(Actor::class)->findOneBy(['link' => $actorItem['actorLink']]);

        if (null === $actor) {
            $actor = new Actor(
                $actorItem['actorName'],
                $actorItem['actorLink'],
                $character,
                $actorItem['seasonsActive'] ?? []
            );
        }

        $manager->persist($actor);

        return $actor;
    }
}
