<?php

declare(strict_types=1);

namespace App\Domain\Employee\Calculator;

use App\Domain\Employee\Entity\Employee;
use App\Domain\Enum\DepartmentBonusTypeEnum;

class FixedBonusStrategy implements BonusStrategyInterface
{
    public function supports(DepartmentBonusTypeEnum $bonusType): bool
    {
        return $bonusType === DepartmentBonusTypeEnum::FIXED_BONUS;
    }

    public function calculate(Employee $employee): int
    {
        $remunerationBase = $employee->getRemunerationBase();
        $employeeDepartment = $employee->getDepartment();

        return $remunerationBase + ($employee->getYearsOfWork() * $employeeDepartment->getBonusValue());
    }
}
