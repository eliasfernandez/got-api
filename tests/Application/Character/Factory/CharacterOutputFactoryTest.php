<?php

namespace App\Tests\Application\Character\Factory;

use App\Application\Actor\Dto\ActorOutputDto;
use App\Application\Actor\Factory\ActorOutputFactory;
use App\Application\Character\Dto\CharacterOutputDto;
use App\Application\Character\Factory\CharacterOutputFactory;
use App\Domain\Actor\Entity\Actor;
use App\Domain\Character\Entity\Character;
use App\Domain\House\Entity\House;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class CharacterOutputFactoryTest extends TestCase
{

    private RouterInterface $router;
    private ActorOutputFactory $actorFactory;
    private EntityManagerInterface $entityManager;
    private CharacterOutputFactory $factory;

    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->actorFactory = $this->createMock(ActorOutputFactory::class);

        $this->factory = new CharacterOutputFactory(
            $this->router,
            $this->actorFactory
        );
    }

    public function testFromEntity(): void
    {
        $actor1 = $this->createMock(Actor::class);
        $actor2 = $this->createMock(Actor::class);

        $this->actorFactory->method('fromEntity')
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
}