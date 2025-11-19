<?php

declare(strict_types=1);

namespace App\Domain\Employee\Calculator\Strategy;

use App\Domain\Employee\Calculator\DTO\AdditionDTO;
use App\Domain\Employee\Calculator\Factory\AdditionDTOFactory;
use App\Domain\Entity\Employee;
use App\Domain\Enum\DepartmentBonusTypeEnum;

class PercentageBonusStrategy implements BonusStrategyInterface
{
    public function __construct(public readonly AdditionDTOFactory $additionDTOFactory)
    {
    }

    public function supports(DepartmentBonusTypeEnum $bonusType): bool
    {
        return DepartmentBonusTypeEnum::PERCENT_BONUS === $bonusType;
    }

    public function calculate(Employee $employee): AdditionDTO
    {
        $remunerationBase = $employee->getRemunerationBase();
        $employeeDepartment = $employee->getDepartment();
        $additionalAmount = $remunerationBase * $employeeDepartment->getBonusValue() / 100;
        $finalRemuneration = $remunerationBase + $additionalAmount;

        return $this->additionDTOFactory->create(
            $additionalAmount,
            $employeeDepartment->getBonusType()->name,
            $finalRemuneration
        );
    }
}
