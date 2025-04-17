<?php

namespace App\Tests\Application\Actor\Factory;

use App\Application\Actor\Dto\ActorOutputDto;
use App\Application\Actor\Factory\ActorOutputFactory;
use App\Domain\Actor\Entity\Actor;
use App\Domain\Character\Entity\Character;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class ActorOutputFactoryTest extends TestCase
{
    private RouterInterface $router;
    private ActorOutputFactory $factory;

    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);

        $this->factory = new ActorOutputFactory($this->router);
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
}