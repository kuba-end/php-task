<?php

declare(strict_types=1);

namespace App\Application\Payroll\Query;


use App\Application\Payroll\Transformer\PayrollTransformer;
use App\Domain\Employee\Calculator\RemunerationCalculator;
use App\Domain\Repository\EmployeeRepositoryInterface;

class GetPayrollQueryHandler
{

    public function __construct(
        private readonly EmployeeRepositoryInterface $employeeRepository,
        private readonly RemunerationCalculator $calculator,
        private readonly PayrollTransformer $payrollTransformer,
    )
    {
    }

    /**
     * @return array<PayrollReportItem>
     */
    public function __invoke(): array
    {
        $employees = $this->employeeRepository->findAll();

        $result = [];
        foreach ($employees as $employee) {
            $finalRemuneration = $this->calculator->calculate($employee);

            $result[] = $this->payrollTransformer->transform($employee, $finalRemuneration);
        }

        return $result;
    }
}
