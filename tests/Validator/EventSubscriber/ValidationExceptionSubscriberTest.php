<?php

/*
 * This file is part of the BoShurikApiBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\ApiBundle\Tests\Validator\EventSubscriber;

use BoShurik\ApiBundle\Validator\EventSubscriber\ValidationExceptionSubscriber;
use BoShurik\ApiBundle\Validator\Exception\ValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ValidationExceptionSubscriberTest extends TestCase
{
    public function testSubscriberServices()
    {
        $this->assertArrayHasKey(ExceptionEvent::class, ValidationExceptionSubscriber::getSubscribedEvents());
    }

    public function testIgnoreNonValidationException()
    {
        $event = $this->createExceptionEvent(new \Exception());
        $subscriber = new ValidationExceptionSubscriber();

        $subscriber->onException($event);

        $this->assertNull($event->getResponse());
        $this->assertFalse($event->isPropagationStopped());
    }

    public function testValidationException()
    {
        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation(
            'message',
            null,
            [],
            new \stdClass(),
            'path.to.field',
            'invalid'
        ));

        $event = $this->createExceptionEvent(new ValidationException($violations));
        $subscriber = new ValidationExceptionSubscriber();

        $subscriber->onException($event);

        $response = $event->getResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertSame('{"errors":{"path.to.field":"message"}}', $response->getContent());
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
