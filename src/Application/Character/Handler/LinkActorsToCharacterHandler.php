<?php

namespace App\Application\Character\Handler;

use App\Application\Character\Command\LinkActorsToCharacterCommand;
use App\Application\Character\Dto\CharacterOutputDto;
use App\Application\Character\Factory\CharacterOutputFactory;
use App\Application\Shared\Utils\UriParser;
use App\Domain\Actor\Entity\Actor;
use App\Domain\Character\Entity\Character;
use App\Domain\Character\Exception\CharacterNotFoundException;
use App\Domain\Character\Exception\LinkedActorNotFoundException;
use App\Domain\Character\Repository\CharacterRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LinkActorsToCharacterHandler
{
    public function __construct(
        private CharacterRepositoryInterface $repository,
        private CharacterOutputFactory $factory,
        private EntityManagerInterface $entityManager,
        private UriParser $uriParser,
    ) {
    }

    public function __invoke(LinkActorsToCharacterCommand $command): CharacterOutputDto
    {
        /*
         * @var Character $character
         */
        $character = $this->repository->getById($command->id) ?? throw new CharacterNotFoundException($command->id);

        $character->emptyActors();

        foreach ($command->actorUris as $uri) {
            $actorId = $this->uriParser->getIdFromUri($uri);

            /*
             * @var ?Actor $actor
             */
            $actor = $this->entityManager->find(Actor::class, $actorId);
            if (!$actor instanceof Actor) {
                throw new LinkedActorNotFoundException($actorId);
            }

            $character->addActor($actor);
        }

        $this->entityManager->persist($character);
        $this->entityManager->flush();

        return $this->factory->fromEntity($character);
    }
}
