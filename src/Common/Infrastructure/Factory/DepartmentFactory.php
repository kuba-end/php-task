<?php

namespace App\Common\Infrastructure\Factory;

use App\Domain\Department\Entity\Department;
use App\Domain\Enum\DepartmentBonusTypeEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Department>
 */
final class DepartmentFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

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
        };

        return [
            'name' => self::faker()->text(30),
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
        return $this
            // ->afterInstantiate(function(Department $department): void {})
        ;
    }
}
