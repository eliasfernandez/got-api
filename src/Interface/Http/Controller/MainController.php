<?php

namespace App\Interface\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class MainController extends AbstractController
{
    #[Route('/api', name: 'app_main', methods: ['GET'])]
    public function index(RouterInterface $router): JsonResponse
    {
        return new JsonResponse(
            [
                'actors' => $router->generate('app_actor_list', parameters: ['page' => 1, 'limit' => 20]),
                'characters' => $router->generate('app_character_list', parameters: ['page' => 1, 'limit' => 20]),
                'openapi' => $router->generate('app_openapi'),
            ]
        );
    }

    #[Route('/api/openapi.yaml', name: 'app_openapi', methods: ['GET'])]
    public function openapi(string $projectDir): Response
    {
        return new Response(file_get_contents($projectDir . '/openapi.yaml'), Response::HTTP_OK, ['Content-Type' => 'yaml']);
    }

    #[Route('/api/swagger', name: 'app_swagger', methods: ['GET'])]
    public function swagger(string $projectDir): Response
    {
        return new Response(
            <<<SWAGGER
                            <!DOCTYPE html>
                            <html lang="en">
                            <head>
                              <meta charset="utf-8" />
                              <meta name="viewport" content="width=device-width, initial-scale=1" />                            
                              <meta name="description" content="SwaggerUI" />
                              <title>SwaggerUI</title>
                              <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css" />
                            </head>
                            <body>
                            <div id="swagger-ui"></div>
                            <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js" crossorigin></script>
                            <script>
                            
                              window.onload = () => {
                                window.ui = SwaggerUIBundle({
                                  url: 'http://localhost:8080/api/openapi.yaml',
                                  dom_id: '#swagger-ui',
                                });
                            
                              };
                            
                            </script>
                            </body>
                            </html>
                            SWAGGER,
            Response::HTTP_OK
        );
    }
}
