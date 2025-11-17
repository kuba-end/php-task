<?php

declare(strict_types=1);

namespace App\Domain\Employee\Calculator;

use App\Domain\Employee\Entity\Employee;

class RemunerationCalculator
{
    /** @var array<BonusStrategyInterface> */
    private readonly array $strategies;
    public function __construct(iterable $strategies)
    {
        $this->strategies = $strategies;
    }

    public function calculate(Employee $employee): int
    {
        $bonusType = $employee->getDepartment()->getBonusType();

        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($bonusType)) {
                return $strategy->calculate($employee);
            }
        }

        throw new StrategyNotFoundException(
            sprintf(
                "Strategy not found for bonus type %s",
                $bonusType->name
            )
        );
    }
}
