<?php

declare(strict_types=1);

namespace App\Domain\Employee\Calculator\Strategy;

use App\Domain\Employee\Calculator\DTO\AdditionDTO;
use App\Domain\Employee\Calculator\Factory\AdditionDTOFactory;
use App\Domain\Entity\Employee;
use App\Domain\Enum\DepartmentBonusTypeEnum;

class FixedBonusStrategy implements BonusStrategyInterface
{
    private const MAXIMUM_YEAR_NUMBER = 10;

    public function __construct(public readonly AdditionDTOFactory $additionDTOFactory)
    {
    }

    public function supports(DepartmentBonusTypeEnum $bonusType): bool
    {
        return $bonusType === DepartmentBonusTypeEnum::FIXED_BONUS;
    }

    public function calculate(Employee $employee): AdditionDTO
    {
        $remunerationBase = $employee->getRemunerationBase();
        $employeeDepartment = $employee->getDepartment();
        $employeeYearsOfWorks = $employee->getYearsOfWork();

        if ($employeeYearsOfWorks > 10) {
            $employeeYearsOfWorks = self::MAXIMUM_YEAR_NUMBER;
        }

        $additionalAmount = $employeeYearsOfWorks * $employeeDepartment->getBonusValue();
        $finalRemuneration = $remunerationBase + $additionalAmount;

        return $this->additionDTOFactory->create(
            $additionalAmount,
            $employeeDepartment->getBonusType()->name,
            $finalRemuneration
        );
    }
}
