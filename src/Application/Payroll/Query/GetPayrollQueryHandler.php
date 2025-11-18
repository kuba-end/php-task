<?php

declare(strict_types=1);

namespace App\Application\Payroll\Query;


use App\Application\Payroll\Transformer\PayrollTransformer;
use App\Domain\Employee\Calculator\Strategy\RemunerationCalculator;
use App\Domain\Exception\InvalidSortingException;
use App\Domain\Repository\EmployeeRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetPayrollQueryHandler
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $employeeRepository,
        private readonly RemunerationCalculator $calculator,
        private readonly PayrollTransformer $payrollTransformer,
        private readonly SortResolver $sortResolver,
    )
    {
    }

    /**
     * @return array<PayrollReportItem>
     */
    public function __invoke(GetPayrollReportQuery $query): array
    {
        $sort = $this->sortResolver->parseSort($query->sort);

        if ($sort !== null) {
            $this->sortResolver->assertSortable($sort[0]);
        }

        $employees = $this->employeeRepository
            ->findAllFilteredAndSorted($query->filters, $sort, $this->sortResolver);

        $results = [];
        foreach ($employees as $employee) {
            $additionData = $this->calculator->calculate($employee);

            $results[] = $this->payrollTransformer->transform($employee, $additionData);
        }

        if ($sort !== null && $this->sortResolver->isMemorySortable($sort[0])) {
            [$field, $direction] = $sort;

            usort($results, function($a, $b) use ($field, $direction) {
                return $direction === 'ASC'
                    ? $a->{$field} <=> $b->{$field}
                    : $b->{$field} <=> $a->{$field};
            });
        }

        return $results;
    }

    private function parseSort(string $sort): array
    {
        $direction = 'ASC';

        if (str_starts_with($sort, '-')) {
            $direction = 'DESC';
            $sort = substr($sort, 1);
        }

        return [$sort, $direction];
    }
}
