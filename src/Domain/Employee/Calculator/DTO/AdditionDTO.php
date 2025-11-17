<?php

declare(strict_types=1);

namespace App\Domain\Employee\Calculator\DTO;

class AdditionDTO
{
    public function __construct(
        public readonly float $additionalAmount,
        public readonly string $bonusType,
        public readonly float $finalRemuneration,
    ) {
    }
}
