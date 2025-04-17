<?php

namespace App\EventListener;

use App\Application\Shared\Utils\UriParserException;
use App\Domain\Actor\Exception\ActorAlreadyExistsException;
use App\Domain\Actor\Exception\ActorNotFoundException;
use App\Domain\Actor\Exception\LinkedCharacterNotFoundException;
use App\Domain\Character\Exception\CharacterAlreadyExistsException;
use App\Domain\Character\Exception\CharacterNotFoundException;
use App\Domain\Character\Exception\LinkedActorNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class ExceptionListener
{
    private const HANDLED_EXCEPTIONS = [
        ActorAlreadyExistsException::class => 409,
        ActorNotFoundException::class => 404,
        LinkedActorNotFoundException::class => 409,
        LinkedCharacterNotFoundException::class => 409,
        CharacterAlreadyExistsException::class => 409,
        CharacterNotFoundException::class => 404,
        NotNormalizableValueException::class => 400,
        UriParserException::class => 400,
    ];

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if ($exception instanceof HandlerFailedException && $exception->getPrevious()) {
            $exception = $exception->getPrevious();
        }

        if (0 === strpos($request->getPathInfo(), '/api')) {
            $response = new JsonResponse();

            if ($exception instanceof HttpExceptionInterface) {
                $response->setData(
                    [
                        'message' => $exception->getMessage(),
                        'code' => $exception->getStatusCode(),
                    ]
                );

                $response->setStatusCode($exception->getStatusCode());
                $response->headers->replace($exception->getHeaders());
            } elseif (in_array($exception::class, array_keys(self::HANDLED_EXCEPTIONS))) {
                $response->setData(
                    [
                        'message' => $exception->getMessage(),
                        'code' => self::HANDLED_EXCEPTIONS[$exception::class],
                    ]
                );
                $response->setStatusCode(self::HANDLED_EXCEPTIONS[$exception::class]);
            } else {
                $response->setData(
                    [
                        'message' => $exception->getMessage(),
                        'code' => $exception->getCode(),
                        'traces' => $exception->getTrace(),
                    ]
                );
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}
