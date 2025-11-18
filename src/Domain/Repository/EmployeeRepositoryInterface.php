<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Application\Payroll\Query\SortResolver;
use App\Domain\Entity\Employee;

interface EmployeeRepositoryInterface
{
    /**
     * @param array{
     *     string, string
     * }|null $sort
     * @param array{
     *     department?: string,
     *     name?: string,
     *     surname?: string
     * } $filters
     * @return array<Employee>
     */
    public function findAllFilteredAndSorted(array $filters, ?array $sort, SortResolver $resolver): array;
}
