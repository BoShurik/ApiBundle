<?php

/*
 * This file is part of the BoShurikApiBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\ApiBundle\Model\EventSubscriber;

use BoShurik\ApiBundle\Model\Exception\UnexpectedValueException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class UnexpectedValueExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ExceptionEvent::class => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof UnexpectedValueException) {
            return;
        }

        $response = new JsonResponse([
            'error' => [
                'message' => 'Bad request',
            ],
        ], Response::HTTP_BAD_REQUEST);

        $event->setResponse($response);
        $event->stopPropagation();
    }
}
