<?php

declare(strict_types=1);

namespace App\Domain\Employee\Calculator\Strategy;

use App\Domain\Employee\Calculator\DTO\AdditionDTO;
use App\Domain\Entity\Employee;
use App\Domain\Exception\StrategyNotFoundException;

class RemunerationCalculator
{
    /** @var array<BonusStrategyInterface> */
    private readonly iterable $strategies;

    /**
     * @param iterable<BonusStrategyInterface> $strategies
     */
    public function __construct(iterable $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * @throws StrategyNotFoundException
     */
    public function calculate(Employee $employee): AdditionDTO
    {
        $bonusType = $employee->getDepartment()->getBonusType();

        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($bonusType)) {
                return $strategy->calculate($employee);
            }
        }

        throw new StrategyNotFoundException(sprintf('Strategy not found for bonus type %s', $bonusType->name));
    }
}
