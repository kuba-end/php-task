<?php

declare(strict_types=1);

namespace App\Domain\Employee\Calculator;

use App\Domain\Employee\Entity\Employee;
use App\Domain\Enum\DepartmentBonusTypeEnum;

class PercentageBonusStrategy implements BonusStrategyInterface
{

    public function supports(DepartmentBonusTypeEnum $bonusType): bool
    {
        return $bonusType === DepartmentBonusTypeEnum::PERCENT_BONUS;
    }

    public function calculate(Employee $employee): int
    {
        $remunerationBase = $employee->getRemunerationBase();
        $employeeDepartment = $employee->getDepartment();

        return $remunerationBase + ($remunerationBase * $employeeDepartment->getBonusValue());
    }
}
