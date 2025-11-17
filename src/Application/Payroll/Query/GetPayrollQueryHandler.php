<?php

declare(strict_types=1);

namespace App\Application\Payroll\Query;


use App\Application\Payroll\Transformer\PayrollTransformer;
use App\Domain\Employee\Calculator\Strategy\RemunerationCalculator;
use App\Domain\Repository\EmployeeRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
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
    public function __invoke(GetPayrollReportQuery $query): array
    {
        $employees = $this->employeeRepository->findAll();

        $result = [];
        foreach ($employees as $employee) {
            $additionData = $this->calculator->calculate($employee);

            $result[] = $this->payrollTransformer->transform($employee, $additionData);
        }

        return $result;
    }
}
