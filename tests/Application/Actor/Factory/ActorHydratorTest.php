<?php

namespace App\Tests\Application\Actor\Factory;

use App\Application\Actor\Dto\ActorInputDto;
use App\Application\Actor\Factory\ActorHydrator;
use App\Application\Shared\Utils\UriParser;
use App\Domain\Actor\Entity\Actor;
use App\Domain\Character\Entity\Character;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ActorHydratorTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private ActorHydrator $factory;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->factory = new ActorHydrator($this->entityManager, new UriParser());
    }

    public function testFromDtoCreatesNewEntity(): void
    {
        $inputDto = new ActorInputDto(
            name: 'Emilia Clarke',
            character: '/api/character/10',
            link: '/name/emilia-clarke',
            seasons: ['1', '2', '3']
        );

        $character = $this->createMock(Character::class);
        $this->entityManager->method('find')->with(Character::class, 10)->willReturn($character);

        $actor = $this->factory->fromDto($inputDto);

        $this->assertInstanceOf(Actor::class, $actor);
        $this->assertSame('Emilia Clarke', $actor->getName());
        $this->assertSame($character, $actor->getCharacter());
        $this->assertSame('/name/emilia-clarke', $actor->getLink());
        $this->assertSame([1, 2, 3], $actor->getSeasons());
    }

    public function testFromDtoUpdatesExistingEntity(): void
    {
        $inputDto = new ActorInputDto(
            name: 'Emilia Clarke',
            character: '10',
            link: '/name/emilia-clarke',
            seasons: []
        );

        $character = $this->createMock(Character::class);
        $this->entityManager->method('find')->with(Character::class, 10)->willReturn($character);

        $actor = new Actor('Old Name', '/name/old-name', $character);

        $result = $this->factory->fromDto($inputDto, $actor);

        $this->assertSame($actor, $result);
        $this->assertSame('Emilia Clarke', $actor->getName());
        $this->assertSame($character, $actor->getCharacter());
        $this->assertSame('/name/emilia-clarke', $actor->getLink());
        $this->assertSame([], $actor->getSeasons());
    }
}