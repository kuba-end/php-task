<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response;

use App\Presentation\Http\Resource\PayrollResource;
use OpenApi\Attributes as OA;

#[OA\Schema]
class PayrollResponse
{
    public function __construct(
        #[OA\Property()]
        public readonly PayrollResource $data,
    )
    {
    }
}
