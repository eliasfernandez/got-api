<?php

namespace App\Factory;

use App\Dto\Input\CharacterInputDto;
use App\Dto\Input\InputDtoInterface;
use App\Dto\Output\CharacterOutputDto;
use App\Dto\Output\OutputDtoInterface;
use App\Entity\Actor;
use App\Entity\Character;
use App\Entity\EntityInterface;
use App\Entity\House;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

class CharacterDtoFactory implements DtoFactoryInterface
{
    use DtoFactoryTrait;

    public function __construct(
        private RouterInterface $router,
        private ActorDtoFactory $actorDtoFactory,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function fromEntity(EntityInterface $object): OutputDtoInterface
    {
        if (!$object instanceof Character) {
            throw new \InvalidArgumentException('Invalid entity');
        }

        return new CharacterOutputDto(
            uri: $this->router->generate('app_character_show', ['id' => $object->getId()]),
            name: $object->getName(),
            actors: array_values(array_map(
                fn (Actor $actor) => $this->actorDtoFactory->fromEntity($actor),
                $object->getActors()->toArray()
            )),
            link: $object->getLink(),
            royal: $object->isRoyal(),
            nickname: $object->getNickname(),
            kingsguard:  $object->isKingsGuard(),
            thumbnail: $object->getThumbnail(),
            image: $object->getImage(),
            houses: array_values(array_map(
                fn (House $house) => $house->getName(),
                $object->getHouses()->toArray()
            ))
        );
    }

    public function fromDto(InputDtoInterface $dto, ?EntityInterface $object = null): EntityInterface
    {
        if (!$dto instanceof CharacterInputDto) {
            throw new \InvalidArgumentException('Invalid DTO');
        }

        if (!$object instanceof Character && $object !== null) {
            throw new \InvalidArgumentException('Invalid entity');
        }

        if (null === $object) {
            $object = new Character($dto->name, $dto->link);
        }

        $object->setName($dto->name)
            ->setLink($dto->link)
            ->setRoyal($dto->royal)
            ->setNickname($dto->nickname)
            ->setKingsguard($dto->kingsguard)
            ->setThumbnail($dto->thumbnail)
            ->setImage($dto->image)
        ;

        if (count($dto->actors) > 0) {
            $object->emptyActors();
            foreach ($dto->actors as $actor) {
                $id = $this->getIdFromUri($actor);
                $actor = $this->entityManager->find(Actor::class, $id);
                if (null === $actor) {
                    throw new \InvalidArgumentException('Invalid actor');
                }

                $object->addActor($actor);
            }
        }

        if (count($dto->houses) > 0) {
            $object->emptyHouses();
            foreach ($dto->houses as $houseName) {
                $house = $this->entityManager->getRepository(House::class)->findOneBy(
                    ['name' => $houseName]
                );
                if (null === $house) {
                    $house = new House($houseName);
                    $this->entityManager->persist($house);
                }

                $object->addHouse($house);
            }
        }

        return $object;
    }
}