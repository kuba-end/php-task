<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Employee;

interface EmployeeRepositoryInterface
{
    /**
     * @param array<string, mixed>|null $filters
     * @return array<Employee>
     */
    public function findAllFilteredAndSorted(array $filters, ?string $sort = null): array;
}
