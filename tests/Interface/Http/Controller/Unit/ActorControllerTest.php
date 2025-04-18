<?php

namespace App\Tests\Interface\Http\Controller\Unit;

use App\Interface\Http\Controller\ActorController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ActorControllerTest extends TestCase
{
    private MessageBusInterface|MockObject $messageBus;
    private SerializerInterface|MockObject $serializer;

    private ActorController $controller;
    public function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->controller = new ActorController($this->messageBus, $this->serializer);
    }

    /**
     * @dataProvider invalidPageDataProvider
     */
    public function testInvalidPage(int $page)
    {
        $request = new Request(['page' => $page]);
        $this->expectException(BadRequestHttpException::class);

        $this->controller->list($request);
    }

    /**
     * @dataProvider invalidLimitDataProvider
     */
    public function testInvalidLimit(int $limit)
    {
        $request = new Request(['limit' => $limit]);
        $this->expectException(BadRequestHttpException::class);

        $this->controller->list($request);
    }

    public static function invalidLimitDataProvider(): array
    {
        return [
            [0],
            [-10],
            [101]
        ];
    }

    public static function invalidPageDataProvider(): array
    {
        return [
            [0],
            [-10]
        ];
    }

}
