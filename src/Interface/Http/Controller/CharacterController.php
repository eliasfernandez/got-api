<?php

namespace App\Interface\Http\Controller;

use App\Application\Character\Command\CreateCharacterCommand;
use App\Application\Character\Command\DeleteCharacterCommand;
use App\Application\Character\Command\LinkActorsToCharacterCommand;
use App\Application\Character\Command\UpdateCharacterCommand;
use App\Application\Character\Dto\CharacterInputDto;
use App\Application\Character\Query\GetCharacterByIdQuery;
use App\Application\Character\Query\ListCharactersQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CharacterController extends ApiController
{
    use HandleTrait;

    public function __construct(
        private MessageBusInterface $messageBus,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/api/character', name: 'app_character_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $page = $this->getPage($request);
        $limit = $this->getLimit($request);

        $query = new ListCharactersQuery(
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

    #[Route('/api/character/{id}', name: 'app_character_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $dto = $this->handle(new GetCharacterByIdQuery($id));

        return $this->json($dto, Response::HTTP_OK);
    }

    #[Route('/api/character', name: 'app_character_add', methods: ['POST'])]
    #[Route('/api/character/{id}', name: 'app_character_edit', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function upsert(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $dto = $this->serializer->deserialize($request->getContent(), CharacterInputDto::class, 'json');
            $command = new CreateCharacterCommand($dto);
            $output = $this->handle($command);
            $statusCode = Response::HTTP_CREATED;
        } else {
            $id = $request->attributes->getInt('id');
            $dto = $this->serializer->deserialize($request->getContent(), CharacterInputDto::class, 'json');
            $command = new UpdateCharacterCommand($id, $dto);
            $output = $this->handle($command);
            $statusCode = Response::HTTP_OK;
        }

        return $this->json($output, $statusCode);
    }

    #[Route('/api/character/{id}', name: 'app_character_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function remove(Request $request): Response
    {
        $command = new DeleteCharacterCommand($request->attributes->getInt('id'));
        $this->handle($command);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/character/{id}/link', name: 'app_character_add_actors', methods: ['POST'])]
    public function link(Request $request, int $id): Response
    {
        $actorUris = $request->getPayload()->all();

        $command = new LinkActorsToCharacterCommand($id, $actorUris);
        $dto = $this->handle($command);

        return $this->json($dto);
    }
}
