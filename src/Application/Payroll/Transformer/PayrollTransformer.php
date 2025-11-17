<?php

declare(strict_types=1);

namespace App\Application\Payroll\Transformer;

use App\Application\Payroll\Query\PayrollReportItem;
use App\Domain\Employee\Calculator\DTO\AdditionDTO;
use App\Domain\Entity\Employee;

class PayrollTransformer
{
    public function transform(Employee $employee, AdditionDTO $additionDTO): PayrollReportItem
    {
        return new PayrollReportItem(
            employeeId: $employee->getId(),
            name: $employee->getName(),
            surname: $employee->getSurname(),
            department: $employee->getDepartment()->getName(),
            baseRemuneration: $employee->getRemunerationBase(),
            additionAmount: $additionDTO->additionalAmount,
            bonusType: $additionDTO->bonusType,
            finalRemuneration: $additionDTO->finalRemuneration,
        );
    }
}
