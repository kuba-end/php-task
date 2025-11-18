<?php

namespace App\Common\Infrastructure\Factory;

use App\Domain\Entity\Department;
use App\Domain\Enum\DepartmentBonusTypeEnum;
use LogicException;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Department>
 */
final class DepartmentFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Department::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        $bonusType = self::faker()->randomElement(DepartmentBonusTypeEnum::cases());

        $bonusValue = match ($bonusType) {
            DepartmentBonusTypeEnum::FIXED_BONUS   => self::faker()->numberBetween(100, 1000),
            DepartmentBonusTypeEnum::PERCENT_BONUS => self::faker()->numberBetween(1, 50),
            default => throw new LogicException('Unknown bonus type'),
        };

        return [
            'name' => self::faker()->colorName(),
            'bonusType' => $bonusType,
            'bonusValue' => $bonusValue,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
