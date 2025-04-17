<?php

namespace App\Tests\Factory;

use App\Dto\Input\ActorInputDto;
use App\Dto\Input\CharacterInputDto;
use App\Dto\Output\ActorOutputDto;
use App\Dto\Output\CharacterOutputDto;
use App\Entity\Actor;
use App\Entity\Character;
use App\Entity\House;
use App\Factory\ActorDtoFactory;
use App\Factory\CharacterDtoFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Doctrine\Common\Collections\ArrayCollection;

class CharacterDtoFactoryTest extends TestCase
{

    private RouterInterface $router;
    private ActorDtoFactory $actorDtoFactory;
    private EntityManagerInterface $entityManager;
    private CharacterDtoFactory $factory;

    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->actorDtoFactory = $this->createMock(ActorDtoFactory::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->factory = new CharacterDtoFactory(
            $this->router,
            $this->actorDtoFactory,
            $this->entityManager
        );
    }

    public function testFromEntity(): void
    {
        $actor1 = $this->createMock(Actor::class);
        $actor2 = $this->createMock(Actor::class);

        $this->actorDtoFactory->method('fromEntity')
            ->willReturn($this->createStub(ActorOutputDto::class));

        $character = $this->createMock(Character::class);
        $character->method('getId')->willReturn(10);
        $character->method('getName')->willReturn('Brienne of Tarth');
        $character->method('getActors')->willReturn(new ArrayCollection([$actor1, $actor2]));
        $character->method('getLink')->willReturn('/name/brienne/');
        $character->method('isRoyal')->willReturn(false);
        $character->method('getNickname')->willReturn('Lady Brienne');
        $character->method('isKingsGuard')->willReturn(true);
        $character->method('getThumbnail')->willReturn('/images/brienne-thumb.jpg');
        $character->method('getImage')->willReturn('/images/brienne.jpg');
        $character->method('getHouses')->willReturn(new ArrayCollection(
            [new House('Tarth')]
        ));

        $this->router->method('generate')
            ->with('app_character_show', ['id' => 10])
            ->willReturn('/api/character/10');

        $dto = $this->factory->fromEntity($character);

        $this->assertInstanceOf(CharacterOutputDto::class, $dto);
        $this->assertSame('/api/character/10', $dto->uri);
        $this->assertSame('Brienne of Tarth', $dto->name);
        $this->assertSame('Tarth', $dto->houses[0]);
        $this->assertCount(2, $dto->actors);
        $this->assertCount(1, $dto->houses);
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

    public function testInvalidDtoInput(): void
    {
        $inputDto = new ActorInputDto('name', '/api/character/121', '/link');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid DTO');

        $this->factory->fromDto($inputDto);
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


        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid entity');

        $this->factory->fromDto($inputDto, $this->createMock(Actor::class));
    }
}