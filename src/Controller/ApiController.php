<?php

namespace App\Controller;

use App\Entity\EntityInterface;
use App\Factory\DtoFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

abstract class ApiController extends AbstractController
{
    protected const PAGE_DEFAULT = 1;
    protected const MAX_RESULTS_PER_PAGE = 20;


    protected EntityRepository $repository;

    public function __construct(
        private DtoFactoryInterface      $dtoFactory,
        protected EntityManagerInterface $entityManager,
        private SerializerInterface      $serializer
    ) {
        $this->repository = $this->entityManager->getRepository(static::CONTROLLER_CLASS);
    }

    protected function getPaginatedResponse(iterable $result, $page, $total, $limit): Response
    {
        $last = (int) ceil($total / $limit);
        if ($page > $last) {
            throw new NotFoundHttpException('This page doesn\'t exists');
        }

        return new Response(
            $this->serializer->serialize(
                [
                    'last' => $last,
                    'page' => $page,
                    'results' => array_map(
                        fn (object $object) => $this->dtoFactory->fromEntity($object),
                        iterator_to_array($result)
                    )
                ],
                'json',
                [
                    AbstractObjectNormalizer::SKIP_NULL_VALUES => true
                ]
            ),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }


    protected function getSingleResponse(object $result, string $statusCode = Response::HTTP_OK): Response
    {

        return new Response(
            $this->serializer->serialize(
                $result,
                'json',
                [
                    AbstractObjectNormalizer::SKIP_NULL_VALUES => true
                ]
            ),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }

    protected function getEntityFromPayload(Request $request): EntityInterface
    {
        $dto = $this->serializer->denormalize($request->getPayload()->all(), static::INPUT_CLASS, 'json');
        $object = $this->getEntity($request);

        return $this->dtoFactory->fromDto($dto, $object);
    }

    protected function getEntity(Request $request): ?EntityInterface
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $this->checkUniqueness($request);
        }

        if (
            in_array($request->getMethod(), [Request::METHOD_PUT, Request::METHOD_DELETE]) &&
            $request->attributes->has('id')
        ) {
            return $this->repository->find($request->attributes->getInt('id')) ?? throw new NotFoundHttpException('Resource not found');
        }

        return null;
    }

    protected function persist(EntityInterface $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    protected function delete(EntityInterface $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    protected function getPage(Request $request): int
    {
        try {
            return $request->query->getInt('page', self::PAGE_DEFAULT);

        } catch (\Exception $exception) {
            throw new BadRequestHttpException('Invalid `page` request');
        }
    }

    protected function getLimit(Request $request): int
    {
        try {
            return $request->query->getInt('limit', self::MAX_RESULTS_PER_PAGE);

        } catch (\Exception $exception) {
            throw new BadRequestHttpException('Invalid `limit` request');
        }
    }

    protected function checkUniqueness(Request $request): void
    {
        if (
            $this->repository->findOneBy(['name' => $request->getPayload()->get('name')]) !== null ||
            $this->repository->findOneBy(['link' => $request->getPayload()->get('link')]) !== null
        ) {
            throw new ConflictHttpException('This item already exists');
        }
    }
}