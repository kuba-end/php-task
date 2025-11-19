<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Employee;

interface EmployeeRepositoryInterface
{
    /**
     * @param array{
     *     department?: string,
     *     name?: string,
     *     surname?: string
     * } $filters
     *
     * @return array<Employee>
     */
    public function findAllFilteredAndSorted(
        array $filters = [],
        ?string $sortField = null,
        ?string $direction = null,
    ): array;
}
