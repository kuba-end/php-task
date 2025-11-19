<?php

declare(strict_types=1);

namespace App\Presentation\Http\Transformer;

use App\Application\Payroll\Query\PayrollReportItem;
use App\Presentation\Http\Resource\PayrollResource;
use App\Presentation\Http\Response\PayrollAttributes;
use App\Presentation\Http\Response\PayrollResponse;

class PayrollResponseTransformer
{
    private const PAYROLL = 'payroll';

    /**
     * @param array<PayrollReportItem> $payrollItems
     *
     * @return array<PayrollResponse>
     */
    public function transform(array $payrollItems): array
    {
        $resources = [];
        foreach ($payrollItems as $payrollItem) {
            $resources[] = new PayrollResponse(
                data: new PayrollResource(
                    id: $payrollItem->employeeId,
                    type: self::PAYROLL,
                    attributes: new PayrollAttributes(
                        name: $payrollItem->name,
                        surname: $payrollItem->surname,
                        department: $payrollItem->department,
                        remunerationBase: $payrollItem->baseRemuneration,
                        additionAmount: $payrollItem->additionAmount,
                        bonusType: $payrollItem->bonusType,
                        finalRemuneration: $payrollItem->finalRemuneration,
                    )
                ));
        }

        return $resources;
    }
}
