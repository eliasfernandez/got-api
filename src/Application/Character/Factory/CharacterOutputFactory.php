<?php

namespace App\Application\Character\Factory;

use App\Application\Actor\Factory\ActorOutputFactory;
use App\Application\Character\Dto\CharacterOutputDto;
use App\Domain\Character\Entity\Character;
use Symfony\Component\Routing\RouterInterface;

class CharacterOutputFactory
{
    public function __construct(
        private RouterInterface $router,
        private ActorOutputFactory $actorFactory,
    ) {
    }

    public function fromEntity(Character $character): CharacterOutputDto
    {
        return new CharacterOutputDto(
            uri: $this->router->generate('app_character_show', ['id' => $character->getId()]),
            name: $character->getName(),
            actors: array_map(
                fn ($actor) => $this->actorFactory->fromEntity($actor),
                $character->getActors()->toArray()
            ),
            link: $character->getLink(),
            royal: $character->isRoyal(),
            nickname: $character->getNickname(),
            kingsguard: $character->isKingsGuard(),
            thumbnail: $character->getThumbnail(),
            image: $character->getImage(),
            houses: array_map(fn ($house) => $house->getName(), $character->getHouses()->toArray())
        );
    }
}
