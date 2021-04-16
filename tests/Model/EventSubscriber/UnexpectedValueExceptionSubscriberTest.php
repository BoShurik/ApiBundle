<?php
/**
 * User: boshurik
 * Date: 16.04.2021
 * Time: 16:37
 */

namespace BoShurik\ApiBundle\Tests\Model\EventSubscriber;

use BoShurik\ApiBundle\Model\EventSubscriber\UnexpectedValueExceptionSubscriber;
use BoShurik\ApiBundle\Model\Exception\UnexpectedValueException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class UnexpectedValueExceptionSubscriberTest extends TestCase
{
    public function testSubscriberServices()
    {
        $this->assertArrayHasKey(ExceptionEvent::class, UnexpectedValueExceptionSubscriber::getSubscribedEvents());
    }

    public function testIgnoreNonValidationException()
    {
        $event = $this->createExceptionEvent(new \Exception());
        $subscriber = new UnexpectedValueExceptionSubscriber();

        $subscriber->onException($event);

        $this->assertNull($event->getResponse());
        $this->assertFalse($event->isPropagationStopped());
    }

    public function testValidationException()
    {
        $event = $this->createExceptionEvent(new UnexpectedValueException());
        $subscriber = new UnexpectedValueExceptionSubscriber();

        $subscriber->onException($event);

        $response = $event->getResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertSame('{"error":{"message":"Bad request"}}', $response->getContent());
        $this->assertTrue($event->isPropagationStopped());
    }

    private function createExceptionEvent(\Throwable $exception): ExceptionEvent
    {
        return new ExceptionEvent(
            new class() implements HttpKernelInterface {
                public function handle(Request $request, int $type = self::MASTER_REQUEST, bool $catch = true): void
                {
                }
            },
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );
    }
}
