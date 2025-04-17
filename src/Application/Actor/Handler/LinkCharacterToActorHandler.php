<?php

namespace App\Application\Actor\Handler;

use App\Application\Actor\Command\LinkCharacterToActorCommand;
use App\Application\Actor\Dto\ActorOutputDto;
use App\Application\Actor\Factory\ActorOutputFactory;
use App\Application\Shared\Utils\UriParser;
use App\Domain\Actor\Entity\Actor;
use App\Domain\Actor\Exception\ActorNotFoundException;
use App\Domain\Actor\Exception\LinkedCharacterNotFoundException;
use App\Domain\Actor\Repository\ActorRepositoryInterface;
use App\Domain\Character\Entity\Character;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LinkCharacterToActorHandler
{
    public function __construct(
        private ActorRepositoryInterface $repository,
        private ActorOutputFactory $factory,
        private EntityManagerInterface $entityManager,
        private UriParser $uriParser,
    ) {
    }

    public function __invoke(LinkCharacterToActorCommand $command): ActorOutputDto
    {
        /*
         * @var Actor $actor
         */
        $actor = $this->repository->getById($command->id) ?? throw new ActorNotFoundException($command->id);

        $characterId = $this->uriParser->getIdFromUri(current($command->characterUris));
        $character = $this->entityManager->find(Character::class, $characterId);
        if (!$character instanceof Character) {
            throw new LinkedCharacterNotFoundException($characterId);
        }

        $actor->setCharacter($character);

        $this->entityManager->persist($actor);
        $this->entityManager->flush();

        return $this->factory->fromEntity($actor);
    }
}
