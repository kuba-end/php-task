<?php

declare(strict_types=1);

namespace App\Infrastructure\Listener;

use App\Domain\Exception\InvalidSortingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class QueryExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        if ($e instanceof HandlerFailedException && null !== $e->getPrevious()) {
            $e = $e->getPrevious();
        }

        if ($e instanceof InvalidSortingException) {
            $response = new JsonResponse([
                'errors' => [
                    [
                        'status' => '400',
                        'title' => 'Invalid sorting',
                        'detail' => $e->getMessage(),
                    ],
                ],
            ], 400);

            $event->setResponse($response);
        } else {
            $response = new JsonResponse([
                'errors' => [
                    [
                        'status' => '500',
                        'title' => 'Internal server error',
                    ],
                ],
            ], 500);

            $event->setResponse($response);
        }
    }
}
