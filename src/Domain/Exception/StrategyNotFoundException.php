<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Symfony\Component\HttpFoundation\Response;

class StrategyNotFoundException extends \Exception
{
    public const EXCEPTION_MESSAGE = 'Invalid sorting argument provided';

    public function __construct(
        string $message = self::EXCEPTION_MESSAGE,
        int $code = Response::HTTP_OK,
    ) {
        parent::__construct($message, $code);
    }
}
