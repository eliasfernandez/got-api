<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Character;
use App\Entity\CharacterImage;
use App\Entity\House;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $json = json_decode(file_get_contents(__DIR__ . '/Resources/got-characters.json'), true, 512, JSON_THROW_ON_ERROR);
        foreach ($json['characters'] as $characterItem) {
            $character = new Character(
                $characterItem['characterName'],
                $characterItem['characterLink'] ?? null,
                $characterItem['royal'] ?? false,
                $characterItem['nickname'] ?? null,
                $characterItem['kingsguard'] ?? false,
                isset($characterItem['characterImageThumb']) ? new CharacterImage($characterItem['characterName'], $characterItem['characterImageThumb']) : null,
                isset($characterItem['characterImageFull']) ? new CharacterImage($characterItem['characterName'], $characterItem['characterImageFull']) : null,
            );

            $manager->persist($character);

            if (!empty($characterItem['actorName'])) {
                $actor = new Actor(
                    $character,
                    $characterItem['actorName'],
                    $characterItem['actorLink'] ?? null,
                    []
                );
                $manager->persist($actor);
            }

            if (!empty($characterItem['actors']) && is_array($characterItem['actors'])) {
                foreach ($characterItem['actors'] as $actorItem) {
                    $actor = new Actor(
                        $character,
                        $actorItem['actorName'],
                        $actorItem['actorLink'] ?? null,
                        $actorItem['seasonsActive'] ?? []
                    );
                    $manager->persist($actor);
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
}
