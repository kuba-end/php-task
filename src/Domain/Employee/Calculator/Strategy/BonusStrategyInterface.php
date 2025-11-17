<?php

namespace App\Domain\Employee\Calculator\Strategy;

use App\Domain\Employee\Calculator\DTO\AdditionDTO;
use App\Domain\Entity\Employee;
use App\Domain\Enum\DepartmentBonusTypeEnum;

interface BonusStrategyInterface
{
    public function supports(DepartmentBonusTypeEnum $bonusType): bool;

    public function calculate(Employee $employee): AdditionDTO;
}
