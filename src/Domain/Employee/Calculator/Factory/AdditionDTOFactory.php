<?php

declare(strict_types=1);

namespace App\Domain\Employee\Calculator\Factory;

use App\Domain\Employee\Calculator\DTO\AdditionDTO;

class AdditionDTOFactory
{
    public function create(
        float $additionalAmount,
        string $bonusType,
        float $finalRemuneration,
    ): AdditionDTO
    {
        return new AdditionDTO(
            $additionalAmount,
            $bonusType,
            $finalRemuneration
        );
    }
}
