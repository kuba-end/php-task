<?php

declare(strict_types=1);

namespace App\Infrastructure\Listener;

use App\Domain\Exception\InvalidSortingException;
use Doctrine\ORM\Query\QueryException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

class QueryExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        if ($e instanceof InvalidSortingException) {
            $response = new JsonResponse([
                'errors' => [
                    [
                        'status' => '400',
                        'title' => 'Invalid field in sort/filter',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], 400);

            $event->setResponse($response);
        }

        if ($e instanceof ExceptionInterface) {
            $response = new JsonResponse([
                'errors' => [
                    [
                        'status' => '500',
                        'title' => 'Internal server error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], 500);

            $event->setResponse($response);
        }
    }
}
