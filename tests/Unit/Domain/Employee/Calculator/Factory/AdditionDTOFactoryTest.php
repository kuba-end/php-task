<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Employee\Calculator\Factory;

use App\Domain\Employee\Calculator\DTO\AdditionDTO;
use App\Domain\Employee\Calculator\Factory\AdditionDTOFactory;
use PHPUnit\Framework\TestCase;

class AdditionDTOFactoryTest extends TestCase
{
    private AdditionDTOFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new AdditionDTOFactory();
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        float $additionalAmount,
        string $bonusType,
        float $finalRemuneration,
        float $expectedAdditionalAmount,
        string $expectedBonusType,
        float $expectedFinalRemuneration
    ): void {
        $result = $this->factory->create($additionalAmount, $bonusType, $finalRemuneration);

        $this->assertInstanceOf(AdditionDTO::class, $result);
        $this->assertSame($expectedAdditionalAmount, $result->additionalAmount);
        $this->assertSame($expectedBonusType, $result->bonusType);
        $this->assertSame($expectedFinalRemuneration, $result->finalRemuneration);
    }

    /**
     * @return array<string, array{float, string, float, float, string, float}>
     */
    public static function createDataProvider(): array
    {
        return [
            'normal values' => [
                1000.0,         // additionalAmount
                'FIXED_BONUS',  // bonusType
                11000.0,        // finalRemuneration
                1000.0,         // expectedAdditionalAmount
                'FIXED_BONUS',  // expectedBonusType
                11000.0,        // expectedFinalRemuneration
            ],
            'zero additional amount' => [
                0.0,
                'PERCENT_BONUS',
                10000.0,
                0.0,
                'PERCENT_BONUS',
                10000.0,
            ],
            'zero final remuneration' => [
                0.0,
                'FIXED_BONUS',
                0.0,
                0.0,
                'FIXED_BONUS',
                0.0,
            ],
            'negative additional amount' => [
                -500.0,
                'FIXED_BONUS',
                9500.0,
                -500.0,
                'FIXED_BONUS',
                9500.0,
            ],
            'large values' => [
                50000.0,
                'PERCENT_BONUS',
                150000.0,
                50000.0,
                'PERCENT_BONUS',
                150000.0,
            ],
            'fractional values' => [
                1234.56,
                'FIXED_BONUS',
                11234.56,
                1234.56,
                'FIXED_BONUS',
                11234.56,
            ],
        ];
    }

    public function testCreateReturnsNewInstance(): void
    {
        $dto1 = $this->factory->create(1000.0, 'FIXED_BONUS', 11000.0);
        $dto2 = $this->factory->create(2000.0, 'PERCENT_BONUS', 12000.0);

        $this->assertNotSame($dto1, $dto2);
        $this->assertSame(1000.0, $dto1->additionalAmount);
        $this->assertSame(2000.0, $dto2->additionalAmount);
    }
}

