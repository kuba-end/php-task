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
        public readonly string $department,
        public readonly float $baseRemuneration,
        public readonly float $additionAmount,
        public readonly string $bonusType,
        public readonly float $finalRemuneration,
    ) {
    }

    public function get(string $field): mixed
    {
        if (!property_exists($this, $field)) {
            throw new \InvalidArgumentException("Field '$field' does not exist in PayrollReportItem.");
        }

        return $this->{$field};
    }
}
