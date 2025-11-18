<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Employee\Calculator\Strategy;

use App\Domain\Employee\Calculator\DTO\AdditionDTO;
use App\Domain\Employee\Calculator\Factory\AdditionDTOFactory;
use App\Domain\Employee\Calculator\Strategy\BonusStrategyInterface;
use App\Domain\Employee\Calculator\Strategy\FixedBonusStrategy;
use App\Domain\Employee\Calculator\Strategy\PercentageBonusStrategy;
use App\Domain\Employee\Calculator\Strategy\RemunerationCalculator;
use App\Domain\Entity\Department;
use App\Domain\Entity\Employee;
use App\Domain\Enum\DepartmentBonusTypeEnum;
use App\Domain\Exception\StrategyNotFoundException;
use PHPUnit\Framework\TestCase;

class RemunerationCalculatorTest extends TestCase
{
    private RemunerationCalculator $calculator;
    private AdditionDTOFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new AdditionDTOFactory();
        
        $fixedStrategy = new FixedBonusStrategy($this->factory);
        $percentageStrategy = new PercentageBonusStrategy($this->factory);
        
        $this->calculator = new RemunerationCalculator([
            $fixedStrategy,
            $percentageStrategy,
        ]);
    }

    /**
     * @dataProvider calculateWithFixedBonusDataProvider
     */
    public function testCalculateWithFixedBonus(
        int $remunerationBase,
        int $yearsOfWork,
        int $bonusValue,
        float $expectedAdditionalAmount,
        float $expectedFinalRemuneration
    ): void {
        $department = $this->createDepartment(DepartmentBonusTypeEnum::FIXED_BONUS, $bonusValue);
        $employee = $this->createEmployee($remunerationBase, $yearsOfWork, $department);

        $result = $this->calculator->calculate($employee);

        $this->assertInstanceOf(AdditionDTO::class, $result);
        $this->assertSame($expectedAdditionalAmount, $result->additionalAmount);
        $this->assertSame(DepartmentBonusTypeEnum::FIXED_BONUS->name, $result->bonusType);
        $this->assertSame($expectedFinalRemuneration, $result->finalRemuneration);
    }

    /**
     * @return array<string, array{int, int, int, float, float}>
     */
    public static function calculateWithFixedBonusDataProvider(): array
    {
        return [
            'fixed bonus: 5 years, 500 bonus' => [
                10000,
                5,
                500,
                2500.0,
                12500.0,
            ],
            'fixed bonus: 10 years (max), 1000 bonus' => [
                20000,
                10,
                1000,
                10000.0,
                30000.0,
            ],
            'fixed bonus: 15 years (capped at 10), 500 bonus' => [
                10000,
                15,
                500,
                5000.0,
                15000.0,
            ],
        ];
    }

    /**
     * @dataProvider calculateWithPercentageBonusDataProvider
     */
    public function testCalculateWithPercentageBonus(
        int $remunerationBase,
        int $bonusValue,
        float $expectedAdditionalAmount,
        float $expectedFinalRemuneration
    ): void {
        $department = $this->createDepartment(DepartmentBonusTypeEnum::PERCENT_BONUS, $bonusValue);
        $employee = $this->createEmployee($remunerationBase, 5, $department);

        $result = $this->calculator->calculate($employee);

        $this->assertInstanceOf(AdditionDTO::class, $result);
        $this->assertEqualsWithDelta($expectedAdditionalAmount, $result->additionalAmount, 0.01);
        $this->assertSame(DepartmentBonusTypeEnum::PERCENT_BONUS->name, $result->bonusType);
        $this->assertEqualsWithDelta($expectedFinalRemuneration, $result->finalRemuneration, 0.01);
    }

    /**
     * @return array<string, array{int, int, float, float}>
     */
    public static function calculateWithPercentageBonusDataProvider(): array
    {
        return [
            'percentage bonus: 10%' => [
                10000,
                10,
                1000.0,
                11000.0,
            ],
            'percentage bonus: 25%' => [
                20000,
                25,
                5000.0,
                25000.0,
            ],
            'percentage bonus: 50%' => [
                10000,
                50,
                5000.0,
                15000.0,
            ],
        ];
    }

    /**
     * @dataProvider strategySelectionDataProvider
     */
    public function testStrategySelection(
        DepartmentBonusTypeEnum $bonusType,
        string $expectedBonusTypeName
    ): void {
        $department = $this->createDepartment($bonusType, 100);
        $employee = $this->createEmployee(10000, 5, $department);

        $result = $this->calculator->calculate($employee);

        $this->assertInstanceOf(AdditionDTO::class, $result);
        $this->assertSame($expectedBonusTypeName, $result->bonusType);
    }

    /**
     * @return array<string, array{DepartmentBonusTypeEnum, string}>
     */
    public static function strategySelectionDataProvider(): array
    {
        return [
            'selects fixed bonus strategy' => [
                DepartmentBonusTypeEnum::FIXED_BONUS,
                DepartmentBonusTypeEnum::FIXED_BONUS->name,
            ],
            'selects percentage bonus strategy' => [
                DepartmentBonusTypeEnum::PERCENT_BONUS,
                DepartmentBonusTypeEnum::PERCENT_BONUS->name,
            ],
        ];
    }

    public function testCalculateThrowsExceptionWhenNoStrategySupports(): void
    {
        $unsupportedStrategy = $this->createMock(BonusStrategyInterface::class);
        $unsupportedStrategy->method('supports')
            ->willReturn(false);

        $calculator = new RemunerationCalculator([$unsupportedStrategy]);

        $department = $this->createDepartment(DepartmentBonusTypeEnum::FIXED_BONUS, 100);
        $employee = $this->createEmployee(10000, 5, $department);

        $this->expectException(StrategyNotFoundException::class);
        $this->expectExceptionMessage('Strategy not found for bonus type FIXED_BONUS');

        $calculator->calculate($employee);
    }

    public function testCalculateThrowsExceptionWhenNoStrategiesProvided(): void
    {
        $calculator = new RemunerationCalculator([]);

        $department = $this->createDepartment(DepartmentBonusTypeEnum::FIXED_BONUS, 100);
        $employee = $this->createEmployee(10000, 5, $department);

        $this->expectException(StrategyNotFoundException::class);
        $this->expectExceptionMessage('Strategy not found for bonus type FIXED_BONUS');

        $calculator->calculate($employee);
    }

    public function testCalculateThrowsExceptionForUnsupportedBonusType(): void
    {
        $percentageStrategy = new PercentageBonusStrategy($this->factory);
        $calculator = new RemunerationCalculator([$percentageStrategy]);

        $department = $this->createDepartment(DepartmentBonusTypeEnum::FIXED_BONUS, 100);
        $employee = $this->createEmployee(10000, 5, $department);

        $this->expectException(StrategyNotFoundException::class);
        $this->expectExceptionMessage('Strategy not found for bonus type FIXED_BONUS');

        $calculator->calculate($employee);
    }

    /**
     * @dataProvider edgeCasesDataProvider
     */
    public function testEdgeCases(
        DepartmentBonusTypeEnum $bonusType,
        int $remunerationBase,
        ?int $yearsOfWork,
        int $bonusValue,
        float $expectedAdditionalAmount,
        float $expectedFinalRemuneration
    ): void {
        $department = $this->createDepartment($bonusType, $bonusValue);
        $employee = $this->createEmployee($remunerationBase, $yearsOfWork, $department);

        $result = $this->calculator->calculate($employee);

        $this->assertInstanceOf(AdditionDTO::class, $result);
        
        if ($bonusType === DepartmentBonusTypeEnum::PERCENT_BONUS) {
            $this->assertEqualsWithDelta($expectedAdditionalAmount, $result->additionalAmount, 0.01);
            $this->assertEqualsWithDelta($expectedFinalRemuneration, $result->finalRemuneration, 0.01);
        } else {
            $this->assertSame($expectedAdditionalAmount, $result->additionalAmount);
            $this->assertSame($expectedFinalRemuneration, $result->finalRemuneration);
        }
    }

    /**
     * @return array<string, array{DepartmentBonusTypeEnum, int, ?int, int, float, float}>
     */
    public static function edgeCasesDataProvider(): array
    {
        return [
            'fixed bonus: zero years of work' => [
                DepartmentBonusTypeEnum::FIXED_BONUS,
                10000,
                0,
                500,
                0.0,
                10000.0,
            ],
            'percentage bonus: zero remuneration' => [
                DepartmentBonusTypeEnum::PERCENT_BONUS,
                0,
                5,
                10,
                0.0,
                0.0,
            ],
            'percentage bonus: zero bonus value' => [
                DepartmentBonusTypeEnum::PERCENT_BONUS,
                10000,
                5,
                0,
                0.0,
                10000.0,
            ],
            'fixed bonus: zero bonus value' => [
                DepartmentBonusTypeEnum::FIXED_BONUS,
                10000,
                5,
                0,
                0.0,
                10000.0,
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

    private function createEmployee(int $remunerationBase, ?int $yearsOfWork, Department $department): Employee
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

