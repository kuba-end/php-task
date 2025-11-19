<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Payroll\Query\GetPayrollReportQuery;
use App\Application\Payroll\Query\PayrollReportItem;
use App\Presentation\Http\Response\PayrollResponse;
use App\Presentation\Http\Transformer\PayrollResponseTransformer;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

#[AsController]
class PayrollController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PayrollResponseTransformer $payrollResponseTransformer
    ) {
    }

    /**
     * @param array{
     *      department?: string,
     *      name?: string,
     *      surname?: string
     *  } $filters
     *
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
            ),
            new OA\Parameter(
                name: 'filter[department]',
                description: 'Filters payroll',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'CadetBlue')
            ),
            new OA\Parameter(
                name: 'filter[name]',
                description: 'Filters payroll',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'Vernon')
            ),
            new OA\Parameter(
                name: 'filter[surname]',
                description: 'Filters payroll',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'Price')
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
    public function getPayrollAction(
        #[MapQueryParameter('sort')] ?string $sort,
        #[MapQueryParameter('filter')] array $filters = []
    ): Response
    {
        $envelope = $this->messageBus->dispatch(
            new GetPayrollReportQuery(
                sort: $sort,
                filters: $filters
            )
        );

        /** @var HandledStamp|null $handled */
        $handled = $envelope->last(HandledStamp::class);

        /** @var PayrollReportItem[] $reports */
        $reports = $handled?->getResult();

        return $this->render('payroll/index.html.twig', [
            'payroll' => $this->payrollResponseTransformer->transform($reports)
        ]);
    }
}
