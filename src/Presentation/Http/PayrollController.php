<?php

declare(strict_types=1);

namespace App\Presentation\Http;

use App\Application\Payroll\Query\GetPayrollReportQuery;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PayrollController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PayrollResponseTransformer $payrollResponseTransformer
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    #[OA\Get(
        path: 'api/payroll',
        summary: 'Get monthly payroll for employees - bonuses included',
        tags: ['Payroll'],
        parameters:[
            new OA\Parameter(
                name: 'sort',
                description: 'Sort order of payroll',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'asc', enum: ['asc', 'desc'])
            )
        ],
        responses: PayrollResponse::class
    )]
    public function createPayrollAction(): JsonResponse
    {
        $report = $this->messageBus->dispatch(
            new GetPayrollReportQuery()
        );

        return new JsonResponse(
            $this->payrollResponseTransformer->transform($report)
        );
    }
}
