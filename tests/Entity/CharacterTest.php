<?php

namespace App\Tests\Entity;

use App\Entity\Actor;
use App\Entity\Character;
use App\Entity\House;
use PHPUnit\Framework\TestCase;

class CharacterTest extends TestCase
{
    public function testCharacterBasicAttributes(): void
    {
        $character = new Character(
            name: 'Jon Snow',
            link: 'https://example.com/jon',
            royal: true,
            nickname: 'Lord Snow',
            kingsguard: false,
            thumbnail: 'thumb.jpg',
            image: 'image.jpg'
        );

        $this->assertSame('Jon Snow', $character->getName());
        $this->assertSame('https://example.com/jon', $character->getLink());
        $this->assertTrue($character->isRoyal());
        $this->assertSame('Lord Snow', $character->getNickname());
        $this->assertFalse($character->isKingsguard());
        $this->assertSame('thumb.jpg', $character->getThumbnail());
        $this->assertSame('image.jpg', $character->getImage());
    }

    public function testSetters(): void
    {
        $character = new Character('Arya Stark', '/character/arya');

        $character->setName('Arya')
                  ->setLink('/character/arya')
                  ->setRoyal(true)
                  ->setKingsguard(true)
                  ->setThumbnail('thumb')
                  ->setImage('img');

        $this->assertSame('Arya', $character->getName());
        $this->assertSame('/character/arya', $character->getLink());
        $this->assertTrue($character->isRoyal());
        $this->assertSame(null, $character->getNickname());
        $this->assertTrue($character->isKingsguard());
        $this->assertSame('thumb', $character->getThumbnail());
        $this->assertSame('img', $character->getImage());
    }

    public function testActorsRelationship()
    {
        $character = new Character('Robb Stark', '/character/rob');
        $actor = $this->createMock(Actor::class);
        $actor->method('getCharacter')->willReturn($character);

        $calls = [];
        $actor->method('setCharacter')
            ->willReturnCallback(function ($characterArg) use (&$calls, $actor) {
                $calls[] = $characterArg;

                return $actor;
            });

        $character->addActor($actor);
        $this->assertCount(1, $character->getActors());

        $character->removeActor($actor);


        $this->assertCount(2, $calls);
        $this->assertSame($character, $calls[0]);
        $this->assertNull($calls[1]);
        $this->assertCount(0, $character->getActors());
    }

    public function testAddRemoveHouse(): void
    {
        $character = new Character('Tyrion Lannister', '/character/tyrion');
        $house = $this->createMock(House::class);

        $this->assertCount(0, $character->getHouses());

        $character->addHouse($house);
        $this->assertCount(1, $character->getHouses());
        $this->assertTrue($character->getHouses()->contains($house));

        $character->removeHouse($house);
        $this->assertCount(0, $character->getHouses());
    }

    public function testBidirectionalParentRelationship(): void
    {
        $parent = new Character('Eddard Stark', '/character/edd');
        $child = new Character('Bran Stark', '/character/bran');

        $child->addParent($parent);

        $this->assertTrue($child->getParents()->contains($parent));
        $this->assertTrue($parent->getParentOf()->contains($child));

        $child->removeParent($parent);
        $this->assertFalse($child->getParents()->contains($parent));
        $this->assertFalse($parent->getParentOf()->contains($child));
    }

    public function testKilledRelationship(): void
    {
        $killer = new Character('Arya Stark', '/character/arya');
        $victim = new Character('Night King', '/character/night');

        $killer->addKilled($victim);

        $this->assertTrue($killer->getKilled()->contains($victim));
        $this->assertTrue($victim->getKilledBy()->contains($killer));

        $killer->removeKilled($victim);

        $this->assertFalse($killer->getKilled()->contains($victim));
        $this->assertFalse($victim->getKilledBy()->contains($killer));
    }

    public function testAlliedToRelationship()
    {
        $main = new Character('Stannis', '/character/stannis');
        $ally = new Character('Davos', '/character/davos');

        $main->addAlly($ally);

        $this->assertTrue($main->getAllies()->contains($ally));
        $this->assertEquals($main, $ally->getAlliedTo());

        $main->removeAlly($ally);

        $this->assertFalse($main->getAllies()->contains($ally));
        $this->assertNull($ally->getAlliedTo());
    }
}