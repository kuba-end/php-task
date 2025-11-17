<?php

namespace App\Domain\Employee\Calculator;

use App\Domain\Employee\Entity\Employee;
use App\Domain\Enum\DepartmentBonusTypeEnum;

interface BonusStrategyInterface
{
    public function supports(DepartmentBonusTypeEnum $bonusType): bool;

    public function calculate(Employee $employee): int;
}
