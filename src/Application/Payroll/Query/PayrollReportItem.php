<?php

declare(strict_types=1);

namespace App\Application\Payroll\Query;


use Symfony\Component\Uid\Uuid;

class PayrollReportItem
{
    public function __construct(
        public readonly Uuid $employeeId,
        public readonly string $name,
        public readonly string $surname,
        public readonly int $baseRemuneration,
        public readonly int $finalRemuneration,
        // czy to zostawic stringiem
        public readonly string $department,
    )
    {
    }
}
