<?php

namespace App\Factory;

use App\Dto\Input\ActorInputDto;
use App\Dto\Input\InputDtoInterface;
use App\Dto\Output\ActorOutputDto;
use App\Dto\Output\OutputDtoInterface;
use App\Entity\Actor;
use App\Entity\Character;
use App\Entity\EntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

class ActorDtoFactory implements DtoFactoryInterface
{
    use DtoFactoryTrait;

    public function __construct(
        private RouterInterface $router,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function fromEntity(EntityInterface $object): OutputDtoInterface
    {
        if (!$object instanceof Actor) {
            throw new \InvalidArgumentException('Invalid entity');
        }

        return new ActorOutputDto(
            $this->router->generate('app_actor_show', ['id' => $object->getId()]),
            $object->getName(),
            $object->getCharacter()?->getName(),
            $object->getLink(),
            empty($object->getSeasons()) ? null : $object->getSeasons(),
        );
    }

    public function fromDto(InputDtoInterface $dto, ?EntityInterface $object = null): EntityInterface
    {
        if (!$dto instanceof ActorInputDto) {
            throw new \InvalidArgumentException('Invalid DTO');
        }

        if (!$object instanceof Actor && $object !== null) {
            throw new \InvalidArgumentException('Invalid entity');
        }

        $id = $this->getIdFromUri($dto->character);

        $character = $this->entityManager->find(Character::class, $id);
        if(!$character instanceof Character) {
            throw new \InvalidArgumentException('Character not found');
        }

        if (null === $object) {
            $object = new Actor($dto->name, $dto->link, $character);
        }

        $object->setCharacter($character)
            ->setName($dto->name)
            ->setLink($dto->link)
            ->setSeasons($dto->seasons);
        ;

        return $object;
    }
}