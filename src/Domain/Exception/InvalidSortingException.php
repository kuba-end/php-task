<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class InvalidSortingException extends BadRequestHttpException
{
    public const EXCEPTION_MESSAGE = 'Invalid sorting argument provided';

    public function __construct(
        string $message = self::EXCEPTION_MESSAGE,

    ) {
        parent::__construct($message);
    }
}
