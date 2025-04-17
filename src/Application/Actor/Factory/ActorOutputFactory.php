<?php

namespace App\Application\Actor\Factory;

use App\Application\Actor\Dto\ActorOutputDto;
use App\Domain\Actor\Entity\Actor;
use Symfony\Component\Routing\RouterInterface;

class ActorOutputFactory
{
    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public function fromEntity(Actor $object): ActorOutputDto
    {
        return new ActorOutputDto(
            $this->router->generate('app_actor_show', ['id' => $object->getId()]),
            $object->getName(),
            $object->getCharacter()?->getName(),
            $object->getLink(),
            empty($object->getSeasons()) ? null : $object->getSeasons(),
        );
    }
}
