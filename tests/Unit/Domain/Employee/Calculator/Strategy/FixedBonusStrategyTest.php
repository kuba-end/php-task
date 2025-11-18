<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Employee\Calculator\Strategy;

use App\Domain\Employee\Calculator\DTO\AdditionDTO;
use App\Domain\Employee\Calculator\Factory\AdditionDTOFactory;
use App\Domain\Employee\Calculator\Strategy\FixedBonusStrategy;
use App\Domain\Entity\Department;
use App\Domain\Entity\Employee;
use App\Domain\Enum\DepartmentBonusTypeEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class FixedBonusStrategyTest extends TestCase
{
    private FixedBonusStrategy $strategy;
    private AdditionDTOFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new AdditionDTOFactory();
        $this->strategy = new FixedBonusStrategy($this->factory);
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(DepartmentBonusTypeEnum $bonusType, bool $expected): void
    {
        $result = $this->strategy->supports($bonusType);
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{DepartmentBonusTypeEnum, bool}>
     */
    public static function supportsDataProvider(): array
    {
        return [
            'supports FIXED_BONUS' => [DepartmentBonusTypeEnum::FIXED_BONUS, true],
            'does not support PERCENT_BONUS' => [DepartmentBonusTypeEnum::PERCENT_BONUS, false],
        ];
    }

    /**
     * @dataProvider calculateDataProvider
     */
    public function testCalculate(
        int $remunerationBase,
        int $yearsOfWork,
        int $bonusValue,
        float $expectedAdditionalAmount,
        float $expectedFinalRemuneration
    ): void {
        $department = $this->createDepartment(DepartmentBonusTypeEnum::FIXED_BONUS, $bonusValue);
        $employee = $this->createEmployee($remunerationBase, $yearsOfWork, $department);

        $result = $this->strategy->calculate($employee);

        $this->assertInstanceOf(AdditionDTO::class, $result);
        $this->assertSame($expectedAdditionalAmount, $result->additionalAmount);
        $this->assertSame(DepartmentBonusTypeEnum::FIXED_BONUS->name, $result->bonusType);
        $this->assertSame($expectedFinalRemuneration, $result->finalRemuneration);
    }

    /**
     * @return array<string, array{int, int, int, float, float}>
     */
    public static function calculateDataProvider(): array
    {
        return [
            'zero years of work' => [
                10000,      // remunerationBase
                0,          // yearsOfWork
                500,        // bonusValue
                0.0,        // expectedAdditionalAmount
                10000.0,    // expectedFinalRemuneration
            ],
            'one year of work' => [
                10000,
                1,
                500,
                500.0,
                10500.0,
            ],
            'five years of work' => [
                10000,
                5,
                500,
                2500.0,
                12500.0,
            ],
            'ten years of work (maximum)' => [
                10000,
                10,
                500,
                5000.0,
                15000.0,
            ],
            'eleven years of work (capped at 10)' => [
                10000,
                11,
                500,
                5000.0,     // Should be capped at 10 * 500
                15000.0,
            ],
            'twenty years of work (capped at 10)' => [
                10000,
                20,
                500,
                5000.0,     // Should be capped at 10 * 500
                15000.0,
            ],
            'high remuneration base with many years' => [
                50000,
                15,
                1000,
                10000.0,    // Capped at 10 * 1000
                60000.0,
            ],
            'low bonus value' => [
                10000,
                5,
                100,
                500.0,
                10500.0,
            ],
            'high bonus value' => [
                10000,
                8,
                2000,
                16000.0,
                26000.0,
            ],
            'zero bonus value' => [
                10000,
                5,
                0,
                0.0,
                10000.0,
            ],
            'zero remuneration base' => [
                0,
                5,
                500,
                2500.0,
                2500.0,
            ],
        ];
    }

    private function createDepartment(DepartmentBonusTypeEnum $bonusType, int $bonusValue): Department
    {
        $department = new Department();
        $department->setName('Test Department');
        $department->setBonusType($bonusType);
        $department->setBonusValue($bonusValue);

        return $department;
    }

    private function createEmployee(int $remunerationBase, int $yearsOfWork, Department $department): Employee
    {
        $employee = new Employee();
        $employee->setName('John');
        $employee->setSurname('Doe');
        $employee->setRemunerationBase($remunerationBase);
        $employee->setYearsOfWork($yearsOfWork);
        $employee->setDepartment($department);

        return $employee;
    }
}

