<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Payroll\Query\GetPayrollReportQuery;
use App\Presentation\Http\Response\PayrollResponse;
use App\Presentation\Http\Transformer\PayrollResponseTransformer;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

#[AsController]
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
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Request successful',
                content: new OA\JsonContent(
                    ref: PayrollResponse::class
                )
            )
        ]
    )]
    public function getPayrollAction(): JsonResponse
    {
        $envelope = $this->messageBus->dispatch(
            new GetPayrollReportQuery()
        );
        /** @var HandledStamp|null $handled */
        $handled = $envelope->last(HandledStamp::class);

        $reports = $handled?->getResult();

        return new JsonResponse(
            $this->payrollResponseTransformer->transform($reports)
        );
    }
}
