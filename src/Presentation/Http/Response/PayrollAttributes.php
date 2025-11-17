<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response;

use OpenApi\Attributes as OA;

#[OA\Schema]
class PayrollAttributes
{
    public function __construct(
        #[OA\Property()]
        public readonly string $name,
        #[OA\Property()]
        public readonly string $surname,
        #[OA\Property()]
        public readonly string $department,
        #[OA\Property()]
        public readonly float $baseRemuneration,
        #[OA\Property()]
        public readonly float $addition,
        #[OA\Property()]
        public readonly string $bonusType,
        #[OA\Property()]
        public readonly float $finalRemuneration,
    )
    {
    }
}
