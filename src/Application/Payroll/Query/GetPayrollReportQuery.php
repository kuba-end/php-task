<?php

declare(strict_types=1);

namespace App\Application\Payroll\Query;

class GetPayrollReportQuery
{
    /**
     * @param array{
     *      department?: string,
     *      name?: string,
     *      surname?: string
     *  } $filters
     */
    public function __construct(
        public readonly ?string $sort = null,
        public readonly array $filters = [],
    ) {
    }
}
