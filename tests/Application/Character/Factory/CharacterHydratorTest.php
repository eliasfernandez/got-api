<?php

namespace App\Tests\Application\Character\Factory;

use App\Application\Character\Dto\CharacterInputDto;
use App\Application\Character\Factory\CharacterHydrator;
use App\Domain\Actor\Entity\Actor;
use App\Domain\Character\Entity\Character;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use TypeError;

class CharacterHydratorTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private CharacterHydrator $factory;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->factory = new CharacterHydrator(
            $this->entityManager
        );
    }

    public function testFromDtoWithActors(): void
    {
        $inputDto = new CharacterInputDto(
            name: 'Brienne of Tarth',
            actors: [
                '/api/actor/101',
                '102'
            ],
            link: '/name/brienne/',
            royal: false,
            nickname: 'Lady Brienne',
            kingsguard: true,
            thumbnail: '/images/brienne-thumb.jpg',
            image: '/images/brienne.jpg'
        );

        $actor1 = $this->createMock(Actor::class);
        $actor2 = $this->createMock(Actor::class);


        $this->entityManager->method('find')
            ->willReturnCallback(function (string $class, $id) use ($actor1, $actor2) {
                return match ((int) $id) {
                    101 => $actor1,
                    102 => $actor2,
                    default => null,
                };
            });

        $character = $this->factory->fromDto($inputDto);

        $this->assertInstanceOf(Character::class, $character);
        $this->assertSame('Brienne of Tarth', $character->getName());
        $this->assertSame('Lady Brienne', $character->getNickname());
        $this->assertSame('/images/brienne.jpg', $character->getImage());

        $actors = $character->getActors();
        $this->assertInstanceOf(ArrayCollection::class, $actors);
        $this->assertCount(2, $actors);
        $this->assertTrue($actors->contains($actor1));
        $this->assertTrue($actors->contains($actor2));
    }

    public function testInvalidObjectInput(): void
    {
        $inputDto = new CharacterInputDto(
            name: 'Brienne of Tarth',
            actors: [
                '/api/actor/101',
                '102'
            ],
            link: '/name/brienne/',
            royal: false,
            nickname: 'Lady Brienne',
            kingsguard: true,
            thumbnail: '/images/brienne-thumb.jpg',
            image: '/images/brienne.jpg'
        );


        $this->expectException(TypeError::class);

        $this->factory->fromDto($inputDto, $this->createMock(Actor::class));
    }
}