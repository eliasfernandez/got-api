<?php

namespace App\Infrastructure\Messenger\Handler;

use App\Infrastructure\Messenger\Message\DeleteMessage;
use App\Infrastructure\Persistence\Elasticsearch\EntityChangeService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityChangeService $entityChangeService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(DeleteMessage $message)
    {
        try {
            $object = $this->entityManager->find($this->getRealClassName($message), $message->getId());
            if (null === $object) {
                $this->logger->error(sprintf('Failed to remove %s from ES', $message->getId()));
            }

            $this->entityChangeService->remove($object);

            return;
        } catch (\Throwable $e) {
            $this->logger->error(
                'Failed to process event',
                [
                    'message' => $e->getMessage(),
                    'type' => $this->getRealClassName($message),
                    'exception' => $e,
                ]
            );
        }
    }

    /**
     * @return class-string
     */
    private function getRealClassName(DeleteMessage $message): string
    {
        return $this->entityManager->getClassMetadata($message->getClass())->getName();
    }
}
