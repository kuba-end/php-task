<?php

namespace App\Common\Infrastructure\Factory;

use App\Domain\Employee\Entity\Employee;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Employee>
 */
final class EmployeeFactory extends PersistentProxyObjectFactory
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
        return Employee::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'department' => DepartmentFactory::random(), // <- key fix
            'name' => self::faker()->text(20),
            'surname' => self::faker()->text(20),
            'remunerationBase' => self::faker()->numberBetween(1000, 10000),
            'yearsOfWork' => self::faker()->numberBetween(1, 20),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Employee $employee): void {})
        ;
    }
}
