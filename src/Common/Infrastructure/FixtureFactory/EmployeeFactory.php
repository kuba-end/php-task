<?php

namespace App\Common\Infrastructure\FixtureFactory;

use App\Domain\Entity\Employee;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Employee>
 */
final class EmployeeFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Employee::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'department' => DepartmentFactory::random(),
            'name' => self::faker()->firstName(),
            'surname' => self::faker()->lastName(20),
            'remunerationBase' => self::faker()->numberBetween(1000, 10000),
            'yearsOfWork' => self::faker()->numberBetween(1, 20),
        ];
    }
}
