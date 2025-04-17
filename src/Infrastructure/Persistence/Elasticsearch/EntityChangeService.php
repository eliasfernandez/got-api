<?php

namespace App\Infrastructure\Persistence\Elasticsearch;

use App\Domain\Shared\EntityInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Elastic\Elasticsearch\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntityChangeService
{
    private const INDEX_NAME = 'got';

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        private Client $client,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private NormalizerInterface $normalizer,
        private string $environment,
    ) {
    }

    public function upsert($entity): void
    {
        $this->syncEntity($entity);
    }

    public function remove($entity): void
    {
        $this->deleteEntity($entity);
    }

    protected function parseEntity($entity): ?array
    {
        if (!$entity instanceof EntityInterface) {
            throw new \InvalidArgumentException(sprintf('Given entity does not implement EntityInterface, %s instead', is_object($entity) ? get_class($entity) : gettype($entity)));
        }

        $class = $this->getRealClassName($entity);

        $data = [];
        $data['timeBased'] = 'true';
        $data['timestamp'] = $entity->getUpdatedAt()->format('c');
        $data['type'] = $this->getSafeName($class, '_');
        $data['entity'] = $this->applyEntityModifications(
            $this->normalizer->normalize(
                $entity,
                'json',
                [
                    'groups' => [sprintf('es:%s', $this->getSafeName($class))],
                ]
            )
        );
        $data['entity']['id'] = $entity->getId();

        return $data;
    }

    private function applyEntityModifications(array $data): array
    {
        $data['env'] = $this->environment;

        return $data;
    }

    public function syncEntity($entity): void
    {
        $data = $this->parseEntity($entity);
        if (null === $data) {
            return;
        }

        try {
            $this->client->index(
                [
                    'index' => $this->getIndex(),
                    'id' => $this->getClassId($entity),
                    'refresh' => true,
                    'version' => $entity->getUpdatedAt()->format('Uu'),
                    'version_type' => 'external',
                    'body' => $data,
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                'Failed to index entity',
                [
                    'exception' => $e,
                    'class' => $this->getRealClassName($entity),
                    'id' => $data['entity']['id'],
                ]
            );
        }
    }

    public function deleteEntity($entity): void
    {
        if (!$entity instanceof EntityInterface) {
            throw new \InvalidArgumentException(sprintf('Given entity is not an object, %s instead', gettype($entity)));
        }

        try {
            $this->client->delete(
                [
                    'index' => $this->getIndex(),
                    'refresh' => true,
                    'id' => $this->getClassId($entity),
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                'Failed to remove entity',
                [
                    'exception' => $e,
                    'class' => $this->getRealClassName($entity),
                    'id' => $entity->getId(),
                ]
            );
        }
    }

    private function getRealClassName(EntityInterface $entity): string
    {
        return $this->entityManager->getClassMetadata(get_class($entity))->getName();
    }

    public function getSafeName(string $class, string $separator = '-'): string
    {
        $className = substr($class, strrpos($class, '\\') + 1);
        $safeName = implode($separator, preg_split('/(?<=[a-z])(?=[A-Z])/', $className));

        return strtolower($safeName);
    }

    public function getIndex(): string
    {
        return sprintf('%s-%s', self::INDEX_NAME, $this->environment);
    }

    private function getClassId(EntityInterface $entity): string
    {
        return sprintf(
            '%s-%s',
            $this->getSafeName($this->getRealClassName($entity), '_'),
            $entity->getId()
        );
    }
}
