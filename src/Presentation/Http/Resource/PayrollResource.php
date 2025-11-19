<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resource;

use App\Presentation\Http\Response\PayrollAttributes;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;

#[OA\Schema]
class PayrollResource
{
    public function __construct(
        #[OA\Property()]
        public readonly Uuid $id,
        #[OA\Property()]
        public string $type,
        #[OA\Property()]
        public readonly PayrollAttributes $attributes,
    )
    {
    }
}
