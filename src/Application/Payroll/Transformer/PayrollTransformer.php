<?php

declare(strict_types=1);

namespace App\Application\Payroll\Transformer;

use App\Application\Payroll\Query\PayrollReportItem;
use App\Domain\Employee\Entity\Employee;

class PayrollTransformer
{
    public function transform(Employee $employee, int $finalRemuneration): PayrollReportItem
    {
        return new PayrollReportItem(
            employeeId: $employee->getId(),
            name: $employee->getName(),
            surname: $employee->getSurname(),
            baseRemuneration: $employee->getRemunerationBase(),
            finalRemuneration: $finalRemuneration,
            department: $employee->getDepartment()->getName(),
        );
    }
}
