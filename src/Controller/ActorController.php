<?php

namespace App\Controller;

use App\Dto\Input\ActorInputDto;
use App\Entity\Actor;
use App\Entity\Character;
use App\Factory\ActorDtoFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ActorController extends ApiController
{
    protected const CONTROLLER_CLASS = Actor::class;
    protected const INPUT_CLASS = ActorInputDto::class;

    public function __construct(
        private readonly ActorDtoFactory $dtoFactory,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ) {
        parent::__construct($dtoFactory, $entityManager, $serializer);
    }

    #[Route('/api/actor', name: 'app_actor_list', methods: ['GET'])]
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

    #[Route('/api/actor/{id}', name: 'app_actor_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $result = $this->repository->find($id);

        if (null === $result) {
            throw $this->createNotFoundException('Actor not found.');
        }

        return $this->getSingleResponse($this->dtoFactory->fromEntity($result));
    }

    #[Route('/api/actor/{id}/link', name: 'app_actor_set_character', methods: ['POST'])]
    public function link(Request $request): Response
    {
        $actor = $this->repository->find($request->attributes->getInt('id')) ?? throw new NotFoundHttpException('Actor to link to not found');
        $characterId = $this->dtoFactory->getIdFromUri(current($request->getPayload()->all()));

        $character = $this->entityManager->find(Character::class, $characterId);
        if(!$character instanceof Character) {
            throw new BadRequestHttpException('Character not found');
        }

        $actor->setCharacter($character);

        $this->persist($actor);

        return $this->getSingleResponse($this->dtoFactory->fromEntity($actor));
    }

    #[Route('/api/actor', name: 'app_actor_add', methods: ['POST'])]
    #[Route('/api/actor/{id}', name: 'app_actor_edit', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function upsert(Request $request): Response
    {
        $actor = $this->getEntityFromPayload($request);
        $this->persist($actor);

        return $this->getSingleResponse(
            $this->dtoFactory->fromEntity($actor),
            $request->isMethod('PUT') ? Response::HTTP_OK : Response::HTTP_CREATED,
        );
    }

    #[Route('/api/actor/{id}', name: 'app_actor_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function remove(Request $request): Response
    {
        $actor = $this->getEntity($request);
        $this->delete($actor);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
