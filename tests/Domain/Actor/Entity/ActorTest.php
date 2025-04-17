<?php

namespace App\Tests\Domain\Actor\Entity;

use App\Domain\Actor\Entity\Actor;
use App\Domain\Character\Entity\Character;
use PHPUnit\Framework\TestCase;

class ActorTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $character = new Character('Tyrion Lannister', '/character/tyrion');
        $actor = new Actor( 'Peter Dinklage', '/name/peter', $character, ['1', '2', '3']);

        $this->assertSame('Peter Dinklage', $actor->getName());
        $this->assertSame('/name/peter', $actor->getLink());
        $this->assertEquals([1, 2, 3], $actor->getSeasons());
        $this->assertSame($character, $actor->getCharacter());
    }

    public function testSetters(): void
    {
        $initialCharacter = new Character('Initial Character', '/character/initial');
        $newCharacter = new Character('New Character', '/character/new');

        $actor = new Actor('Old Name', '/name/old-name', $initialCharacter);
        $actor->setName('New Name');
        $actor->setLink('/name/new-name');
        $actor->setSeasons(['4', '5', '6']);
        $actor->setCharacter($newCharacter);

        $this->assertSame('New Name', $actor->getName());
        $this->assertSame('/name/new-name', $actor->getLink());
        $this->assertEquals([4, 5, 6], $actor->getSeasons());
        $this->assertSame($newCharacter, $actor->getCharacter());
    }

    public function testGetSeasonsCastsToIntegers(): void
    {
        $character = new Character('Daenerys Targaryen', '/character/daenerys');
        $actor = new Actor('Emilia Clarke', '/name/', $character, ['1', '2', '3']);

        $this->assertSame([1, 2, 3], $actor->getSeasons());
    }
}