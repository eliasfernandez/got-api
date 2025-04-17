<?php

namespace App\Interface\Http\Controller;

use App\Application\Character\Query\SearchCharactersQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends ApiController
{
    use HandleTrait;

    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    #[Route('/api/search', name: 'app_character_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $page = $this->getPage($request);
        $limit = $this->getLimit($request);
        $query = $this->getSearchQuery($request);

        $query = new SearchCharactersQuery(
            $page,
            $limit,
            $query
        );

        $result = $this->handle($query);

        $last = (int) ceil($result['total'] / $limit);
        if ($page > $last) {
            throw new NotFoundHttpException('This page doesn\'t exists');
        }

        return $this->json(
            [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total'],
                'last' => ceil($result['total'] / $request->query->getInt('limit', 20)),
                'results' => $result['result'],
            ],
            Response::HTTP_OK
        );
    }
}
