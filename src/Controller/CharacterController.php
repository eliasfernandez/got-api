<?php

namespace App\Controller;

use App\Dto\Input\CharacterInputDto;
use App\Entity\Actor;
use App\Entity\Character;
use App\Factory\CharacterDtoFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class CharacterController extends ApiController
{
    protected const CONTROLLER_CLASS = Character::class;
    protected const INPUT_CLASS = CharacterInputDto::class;

    public function __construct(
        protected readonly CharacterDtoFactory $dtoFactory,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ) {
        parent::__construct($dtoFactory, $entityManager, $serializer);
    }

    #[Route('/api/character', name: 'app_character_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $page = $this->getPage($request);
        $limit = $this->getLimit($request);

        /**
         *  @var int $total
         *  @var Paginator $result
         */
        [$total, $result] = $this->repository->findAllPaginated($page, $limit);

        return $this->getPaginatedResponse(
            $result->getIterator(),
            $page,
            $total,
            $limit
        );
    }

    #[Route('/api/character/{id}', name: 'app_character_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $result = $this->repository->find($id);

        if (null === $result) {
            throw $this->createNotFoundException('Character not found.');
        }

        return $this->getSingleResponse($this->dtoFactory->fromEntity($result));
    }

    #[Route('/api/character/{id}/link', name: 'app_character_add_actors', methods: ['POST'])]
    public function link(Request $request): Response
    {
        /** @var Character $character */
        $character = $this->repository->find($request->attributes->getInt('id')) ?? throw new NotFoundHttpException('Character to link to not found');

        $character->emptyActors();

        foreach ($request->getPayload()->all() as $uri) {
            $actorId = $this->dtoFactory->getIdFromUri($uri);

            /** @var Actor $actor */
            $actor = $this->entityManager->find(Actor::class, $actorId);
            if(!$actor instanceof Actor) {
                throw new BadRequestHttpException('Actor not found');
            }

            $character->addActor($actor);
        }

        $this->persist($character);

        return $this->getSingleResponse($this->dtoFactory->fromEntity($character));
    }

    #[Route('/api/character', name: 'app_character_add', methods: ['POST'])]
    #[Route('/api/character/{id}', name: 'app_character_edit', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function upsert(Request $request): Response
    {
        $character = $this->getEntityFromPayload($request);
        $this->persist($character);

        return $this->getSingleResponse(
            $this->dtoFactory->fromEntity($character),
            $request->isMethod(Request::METHOD_PUT) ? Response::HTTP_OK : Response::HTTP_CREATED,
        );
    }

    #[Route('/api/character/{id}', name: 'app_character_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function remove(Request $request): Response
    {
        $character = $this->getEntity($request);
        $this->delete($character);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
