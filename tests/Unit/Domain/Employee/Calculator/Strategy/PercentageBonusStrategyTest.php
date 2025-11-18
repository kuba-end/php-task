<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Employee\Calculator\Strategy;

use App\Domain\Employee\Calculator\DTO\AdditionDTO;
use App\Domain\Employee\Calculator\Factory\AdditionDTOFactory;
use App\Domain\Employee\Calculator\Strategy\PercentageBonusStrategy;
use App\Domain\Entity\Department;
use App\Domain\Entity\Employee;
use App\Domain\Enum\DepartmentBonusTypeEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class PercentageBonusStrategyTest extends TestCase
{
    private PercentageBonusStrategy $strategy;
    private AdditionDTOFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new AdditionDTOFactory();
        $this->strategy = new PercentageBonusStrategy($this->factory);
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
            'supports PERCENT_BONUS' => [DepartmentBonusTypeEnum::PERCENT_BONUS, true],
            'does not support FIXED_BONUS' => [DepartmentBonusTypeEnum::FIXED_BONUS, false],
        ];
    }

    /**
     * @dataProvider calculateDataProvider
     */
    public function testCalculate(
        int $remunerationBase,
        int $bonusValue,
        float $expectedAdditionalAmount,
        float $expectedFinalRemuneration
    ): void {
        $department = $this->createDepartment(DepartmentBonusTypeEnum::PERCENT_BONUS, $bonusValue);
        $employee = $this->createEmployee($remunerationBase, $department);

        $result = $this->strategy->calculate($employee);

        $this->assertInstanceOf(AdditionDTO::class, $result);
        $this->assertEqualsWithDelta($expectedAdditionalAmount, $result->additionalAmount, 0.01);
        $this->assertSame(DepartmentBonusTypeEnum::PERCENT_BONUS->name, $result->bonusType);
        $this->assertEqualsWithDelta($expectedFinalRemuneration, $result->finalRemuneration, 0.01);
    }

    /**
     * @return array<string, array{int, int, float, float}>
     */
    public static function calculateDataProvider(): array
    {
        return [
            '10% bonus' => [
                10000,      // remunerationBase
                10,         // bonusValue (percentage)
                1000.0,     // expectedAdditionalAmount (10000 * 10 / 100)
                11000.0,    // expectedFinalRemuneration
            ],
            '5% bonus' => [
                10000,
                5,
                500.0,
                10500.0,
            ],
            '25% bonus' => [
                10000,
                25,
                2500.0,
                12500.0,
            ],
            '50% bonus' => [
                10000,
                50,
                5000.0,
                15000.0,
            ],
            '100% bonus (double)' => [
                10000,
                100,
                10000.0,
                20000.0,
            ],
            '1% bonus' => [
                10000,
                1,
                100.0,
                10100.0,
            ],
            '0% bonus' => [
                10000,
                0,
                0.0,
                10000.0,
            ],
            'zero remuneration base' => [
                0,
                10,
                0.0,
                0.0,
            ],
            'high remuneration base with low percentage' => [
                100000,
                5,
                5000.0,
                105000.0,
            ],
            'low remuneration base with high percentage' => [
                1000,
                50,
                500.0,
                1500.0,
            ],
            'fractional result (33.33%)' => [
                10000,
                33,
                3300.0,     // 10000 * 33 / 100 = 3300
                13300.0,
            ],
            'fractional result (7.5%)' => [
                10000,
                7,
                700.0,      // 10000 * 7 / 100 = 700
                10700.0,
            ],
            'very high percentage (200%)' => [
                10000,
                200,
                20000.0,
                30000.0,
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

    private function createEmployee(int $remunerationBase, Department $department): Employee
    {
        $employee = new Employee();
        $employee->setName('John');
        $employee->setSurname('Doe');
        $employee->setRemunerationBase($remunerationBase);
        $employee->setYearsOfWork(5);
        $employee->setDepartment($department);

        return $employee;
    }
}

