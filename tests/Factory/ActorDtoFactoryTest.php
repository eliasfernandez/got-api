<?php

namespace App\Tests\Factory;

use App\Dto\Input\ActorInputDto;
use App\Dto\Output\ActorOutputDto;
use App\Entity\Actor;
use App\Entity\Character;
use App\Factory\ActorDtoFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class ActorDtoFactoryTest extends TestCase
{
    private RouterInterface $router;
    private EntityManagerInterface $entityManager;
    private ActorDtoFactory $factory;

    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->factory = new ActorDtoFactory($this->router, $this->entityManager);
    }

    public function testFromEntity(): void
    {
        $character = $this->createMock(Character::class);
        $character->method('getName')->willReturn('Jon Snow');

        $actor = $this->createMock(Actor::class);
        $actor->method('getId')->willReturn(1);
        $actor->method('getName')->willReturn('Kit Harington');
        $actor->method('getCharacter')->willReturn($character);
        $actor->method('getLink')->willReturn('/name/kit-harrington/');
        $actor->method('getSeasons')->willReturn([1, 2]);

        $this->router->method('generate')
            ->with('app_actor_show', ['id' => 1])
            ->willReturn('/api/actor/1');

        $dto = $this->factory->fromEntity($actor);

        $this->assertInstanceOf(ActorOutputDto::class, $dto);
        $this->assertSame('/api/actor/1', $dto->uri);
        $this->assertSame('Kit Harington', $dto->name);
        $this->assertSame('Jon Snow', $dto->character);
        $this->assertSame('/name/kit-harrington/', $dto->link);
        $this->assertSame([1, 2], $dto->seasons);
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