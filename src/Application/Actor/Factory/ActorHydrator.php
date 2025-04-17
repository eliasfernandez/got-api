<?php

namespace App\Application\Actor\Factory;

use App\Application\Actor\Dto\ActorInputDto;
use App\Application\Shared\Utils\UriParser;
use App\Domain\Actor\Entity\Actor;
use App\Domain\Actor\Exception\LinkedCharacterNotFoundException;
use App\Domain\Character\Entity\Character;
use Doctrine\ORM\EntityManagerInterface;

final class ActorHydrator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UriParser $uriParser,
    ) {
    }

    public function fromDto(ActorInputDto $dto, ?Actor $object = null): Actor
    {
        $id = $this->uriParser->getIdFromUri($dto->character);

        $character = $this->entityManager->find(Character::class, $id);
        if (!$character instanceof Character) {
            throw new LinkedCharacterNotFoundException($id);
        }

        if (null === $object) {
            $object = new Actor($dto->name, $dto->link, $character);
        }

        $object->setCharacter($character)
            ->setName($dto->name)
            ->setLink($dto->link)
            ->setSeasons($dto->seasons);

        return $object;
    }
}
