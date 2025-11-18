<?php

declare(strict_types=1);

namespace App\Application\Payroll\Query;

use App\Domain\Exception\InvalidSortingException;

class SortResolver
{
    private const DB_SORTABLE = [
        'name' => 'e.name',
        'surname' => 'e.surname',
        'remunerationBase' => 'e.remunerationBase',
        'department' => 'd.name',
    ];

    /** Sortable only after transformation */
    private const MEMORY_SORTABLE = [
        'additionAmount',
        'finalRemuneration',
        'bonusType',
    ];

    /**
     * @return array{string, string}|null
     */
    public function parseSort(?string $sort): ?array
    {
        if ($sort === null) {
            return null;
        }

        $direction = 'ASC';

        if (str_starts_with($sort, '-')) {
            $direction = 'DESC';
            $sort = substr($sort, 1);
        }

        return [$sort, $direction];
    }

    public function isDbSortable(string $field): bool
    {
        return array_key_exists($field, self::DB_SORTABLE);
    }

    public function isMemorySortable(string $field): bool
    {
        return in_array($field, self::MEMORY_SORTABLE, true);
    }

    public function getDbSortColumn(string $field): ?string
    {
        return self::DB_SORTABLE[$field] ?? null;
    }

    /**
     * @throws InvalidSortingException
     */
    public function assertSortable(string $field): void
    {
        if (!$this->isDbSortable($field) && !$this->isMemorySortable($field)) {
            throw new InvalidSortingException("Sorting by '$field' is not allowed.");
        }
    }
}
