<?php

namespace App\Interface\Http\Controller;

use App\Application\Actor\Command\CreateActorCommand;
use App\Application\Actor\Command\DeleteActorCommand;
use App\Application\Actor\Command\LinkCharacterToActorCommand;
use App\Application\Actor\Command\UpdateActorCommand;
use App\Application\Actor\Dto\ActorInputDto;
use App\Application\Actor\Query\GetActorByIdQuery;
use App\Application\Actor\Query\ListActorsQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ActorController extends ApiController
{
    use HandleTrait;

    public function __construct(
        private MessageBusInterface $messageBus,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/api/actor', name: 'app_actor_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $page = $this->getPage($request);
        $limit = $this->getLimit($request);

        $query = new ListActorsQuery(
            $page,
            $limit
        );

        $result = $this->handle($query);

        $last = (int) ceil($result['total'] / $limit);
        if ($page > $last) {
            throw new NotFoundHttpException('This page doesn\'t exists');
        }

        return $this->json(
            [
                'page' => $this->getPage($request),
                'limit' => $this->getLimit($request),
                'total' => $result['total'],
                'last' => ceil($result['total'] / $request->query->getInt('limit', 20)),
                'results' => $result['result'],
            ],
            Response::HTTP_OK
        );
    }

    #[Route('/api/actor/{id}', name: 'app_actor_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $dto = $this->handle(new GetActorByIdQuery($id));

        return $this->json($dto, Response::HTTP_OK);
    }

    #[Route('/api/actor', name: 'app_actor_add', methods: ['POST'])]
    #[Route('/api/actor/{id}', name: 'app_actor_edit', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function upsert(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $dto = $this->serializer->deserialize($request->getContent(), ActorInputDto::class, 'json');
            $command = new CreateActorCommand($dto);
            $output = $this->handle($command);
            $statusCode = Response::HTTP_CREATED;
        } else {
            $id = $request->attributes->getInt('id');
            $dto = $this->serializer->deserialize($request->getContent(), ActorInputDto::class, 'json');
            $command = new UpdateActorCommand($id, $dto);
            $output = $this->handle($command);
            $statusCode = Response::HTTP_OK;
        }

        return $this->json($output, $statusCode);
    }

    #[Route('/api/actor/{id}', name: 'app_actor_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function remove(Request $request): Response
    {
        $command = new DeleteActorCommand($request->attributes->getInt('id'));
        $this->handle($command);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/actor/{id}/link', name: 'app_actor_set_character', methods: ['POST'])]
    public function link(Request $request, int $id): Response
    {
        $actorUris = $request->getPayload()->all();

        $command = new LinkCharacterToActorCommand($id, $actorUris);
        $dto = $this->handle($command);

        return $this->json($dto);
    }
}
