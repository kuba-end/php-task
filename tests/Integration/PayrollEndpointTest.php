<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Common\Infrastructure\Factory\DepartmentFactory;
use App\Common\Infrastructure\Factory\EmployeeFactory;
use App\Domain\Enum\DepartmentBonusTypeEnum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class PayrollEndpointTest extends WebTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testGetPayrollReturnsSuccessfulResponse(): void
    {
        $client = static::createClient();

        $department = DepartmentFactory::createOne([
            'name' => 'Engineering',
            'bonusType' => DepartmentBonusTypeEnum::FIXED_BONUS,
            'bonusValue' => 500,
        ]);

        EmployeeFactory::createOne([
            'name' => 'John',
            'surname' => 'Doe',
            'remunerationBase' => 10000,
            'yearsOfWork' => 5,
            'department' => $department,
        ]);

        $client->request('GET', '/api/payroll');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('data', $response[0] ?? []);
    }

    /**
     * @dataProvider sortFieldDataProvider
     */
    public function testSortingByField(string $sortField, string $direction, callable $assertion): void
    {
        $client = static::createClient();

        $dept1 = DepartmentFactory::createOne(['name' => 'Alpha', 'bonusType' => DepartmentBonusTypeEnum::FIXED_BONUS, 'bonusValue' => 500]);
        $dept2 = DepartmentFactory::createOne(['name' => 'Beta', 'bonusType' => DepartmentBonusTypeEnum::PERCENT_BONUS, 'bonusValue' => 10]);

        EmployeeFactory::createOne(['name' => 'Alice', 'surname' => 'Zebra', 'remunerationBase' => 5000, 'yearsOfWork' => 2, 'department' => $dept1]);
        EmployeeFactory::createOne(['name' => 'Bob', 'surname' => 'Alpha', 'remunerationBase' => 10000, 'yearsOfWork' => 8, 'department' => $dept2]);
        EmployeeFactory::createOne(['name' => 'Charlie', 'surname' => 'Beta', 'remunerationBase' => 7500, 'yearsOfWork' => 5, 'department' => $dept1]);

        $sortParam = $direction === 'desc' ? "-{$sortField}" : $sortField;
        $client->request('GET', '/api/payroll', ['sort' => $sortParam]);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
//        if ($sortField === 'additionAmount') {
//            dd($response);
//        }
        $this->assertIsArray($response);
        $this->assertCount(3, $response);

        $assertion($response);
    }

    /**
     * @return array<string, array{string, string, callable}>
     */
    public static function sortFieldDataProvider(): array
    {
        return [
            'sort by name ascending' => [
                'name',
                'asc',
                function (array $response): void {
                    self::assertEquals('Alice', $response[0]['data']['attributes']['name']);
                    self::assertEquals('Bob', $response[1]['data']['attributes']['name']);
                    self::assertEquals('Charlie', $response[2]['data']['attributes']['name']);
                },
            ],
            'sort by name descending' => [
                'name',
                'desc',
                function (array $response): void {
                    self::assertEquals('Charlie', $response[0]['data']['attributes']['name']);
                    self::assertEquals('Bob', $response[1]['data']['attributes']['name']);
                    self::assertEquals('Alice', $response[2]['data']['attributes']['name']);
                },
            ],
            'sort by surname ascending' => [
                'surname',
                'asc',
                function (array $response): void {
                    self::assertEquals('Alpha', $response[0]['data']['attributes']['surname']);
                    self::assertEquals('Beta', $response[1]['data']['attributes']['surname']);
                    self::assertEquals('Zebra', $response[2]['data']['attributes']['surname']);
                },
            ],
            'sort by surname descending' => [
                'surname',
                'desc',
                function (array $response): void {
                    self::assertEquals('Zebra', $response[0]['data']['attributes']['surname']);
                    self::assertEquals('Beta', $response[1]['data']['attributes']['surname']);
                    self::assertEquals('Alpha', $response[2]['data']['attributes']['surname']);
                },
            ],
            'sort by remunerationBase ascending' => [
                'remunerationBase',
                'asc',
                function (array $response): void {
                    self::assertEquals(5000, $response[0]['data']['attributes']['remunerationBase']);
                    self::assertEquals(7500, $response[1]['data']['attributes']['remunerationBase']);
                    self::assertEquals(10000, $response[2]['data']['attributes']['remunerationBase']);
                },
            ],
            'sort by remunerationBase descending' => [
                'remunerationBase',
                'desc',
                function (array $response): void {
                    self::assertEquals(10000, $response[0]['data']['attributes']['remunerationBase']);
                    self::assertEquals(7500, $response[1]['data']['attributes']['remunerationBase']);
                    self::assertEquals(5000, $response[2]['data']['attributes']['remunerationBase']);
                },
            ],
            'sort by department ascending' => [
                'department',
                'asc',
                function (array $response): void {
                    self::assertEquals('Alpha', $response[0]['data']['attributes']['department']);
                    self::assertEquals('Alpha', $response[1]['data']['attributes']['department']);
                    self::assertEquals('Beta', $response[2]['data']['attributes']['department']);
                },
            ],
            'sort by department descending' => [
                'department',
                'desc',
                function (array $response): void {
                    self::assertEquals('Beta', $response[0]['data']['attributes']['department']);
                    self::assertEquals('Alpha', $response[1]['data']['attributes']['department']);
                    self::assertEquals('Alpha', $response[2]['data']['attributes']['department']);
                },
            ],
            'sort by additionAmount ascending' => [
                'additionAmount',
                'asc',
                function (array $response): void {
                    self::assertEquals(1000.0, $response[0]['data']['attributes']['additionAmount']);
                    self::assertEquals(1000.0, $response[1]['data']['attributes']['additionAmount']);
                    self::assertEquals(2500.0, $response[2]['data']['attributes']['additionAmount']);
                },
            ],
            'sort by finalRemuneration ascending' => [
                'finalRemuneration',
                'asc',
                function (array $response): void {
                    $amounts = array_map(fn($item) => $item['data']['attributes']['finalRemuneration'], $response);
                    $sorted = $amounts;
                    sort($sorted);
                    self::assertEquals($sorted, $amounts);
                },
            ],
            'sort by bonusType ascending' => [
                'bonusType',
                'asc',
                function (array $response): void {
                    $types = array_map(fn($item) => $item['data']['attributes']['bonusType'], $response);
                    $sorted = $types;
                    sort($sorted);
                    self::assertEquals($sorted, $types);
                },
            ],
        ];
    }

    /**
     * @dataProvider filterDataProvider
     */
    public function testFiltering(string $filterType, string $filterValue, int $expectedCount): void
    {
        $client = static::createClient();

        $dept1 = DepartmentFactory::createOne(['name' => 'Engineering', 'bonusType' => DepartmentBonusTypeEnum::FIXED_BONUS, 'bonusValue' => 500]);
        $dept2 = DepartmentFactory::createOne(['name' => 'Marketing', 'bonusType' => DepartmentBonusTypeEnum::PERCENT_BONUS, 'bonusValue' => 10]);

        EmployeeFactory::createOne(['name' => 'John', 'surname' => 'Doe', 'remunerationBase' => 10000, 'yearsOfWork' => 5, 'department' => $dept1]);
        EmployeeFactory::createOne(['name' => 'Jane', 'surname' => 'Smith', 'remunerationBase' => 8000, 'yearsOfWork' => 3, 'department' => $dept2]);
        EmployeeFactory::createOne(['name' => 'John', 'surname' => 'Johnson', 'remunerationBase' => 12000, 'yearsOfWork' => 7, 'department' => $dept1]);

        $client->request('GET', '/api/payroll', ['filter' => [$filterType => $filterValue]]);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertCount($expectedCount, $response);
    }

    /**
     * @return array<string, array{string, string, int}>
     */
    public static function filterDataProvider(): array
    {
        return [
            'filter by name' => ['name', 'John', 2],
            'filter by surname' => ['surname', 'Doe', 1],
            'filter by department' => ['department', 'Engineering', 2],
            'filter by non-existent name' => ['name', 'NonExistent', 0],
            'filter by non-existent surname' => ['surname', 'NonExistent', 0],
            'filter by non-existent department' => ['department', 'NonExistent', 0],
        ];
    }

    /**
     * @dataProvider combinedFilterAndSortDataProvider
     */
    public function testCombinedFilterAndSort(array $filters, string $sort, callable $assertion): void
    {
        $client = static::createClient();

        $dept1 = DepartmentFactory::createOne(['name' => 'Engineering', 'bonusType' => DepartmentBonusTypeEnum::FIXED_BONUS, 'bonusValue' => 500]);
        $dept2 = DepartmentFactory::createOne(['name' => 'Marketing', 'bonusType' => DepartmentBonusTypeEnum::PERCENT_BONUS, 'bonusValue' => 10]);

        EmployeeFactory::createOne(['name' => 'John', 'surname' => 'Alpha', 'remunerationBase' => 10000, 'yearsOfWork' => 5, 'department' => $dept1]);
        EmployeeFactory::createOne(['name' => 'John', 'surname' => 'Beta', 'remunerationBase' => 8000, 'yearsOfWork' => 3, 'department' => $dept1]);
        EmployeeFactory::createOne(['name' => 'Jane', 'surname' => 'Gamma', 'remunerationBase' => 12000, 'yearsOfWork' => 7, 'department' => $dept2]);

        $client->request('GET', '/api/payroll', [
            'filter' => $filters,
            'sort' => $sort,
        ]);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);

        $assertion($response);
    }

    /**
     * @return array<string, array{array<string, string>, string, callable}>
     */
    public static function combinedFilterAndSortDataProvider(): array
    {
        return [
            'filter by name and sort by surname' => [
                ['name' => 'John'],
                'surname',
                function (array $response): void {
                    self::assertCount(2, $response);
                    self::assertEquals('Alpha', $response[0]['data']['attributes']['surname']);
                    self::assertEquals('Beta', $response[1]['data']['attributes']['surname']);
                },
            ],
            'filter by department and sort by remunerationBase desc' => [
                ['department' => 'Engineering'],
                '-remunerationBase',
                function (array $response): void {
                    self::assertCount(2, $response);
                    self::assertEquals(10000, $response[0]['data']['attributes']['remunerationBase']);
                    self::assertEquals(8000, $response[1]['data']['attributes']['remunerationBase']);
                },
            ],
            'filter by multiple fields (name and department)' => [
                ['name' => 'John', 'department' => 'Engineering'],
                'surname',
                function (array $response): void {
                    self::assertCount(2, $response);
                    foreach ($response as $item) {
                        self::assertEquals('John', $item['data']['attributes']['name']);
                        self::assertEquals('Engineering', $item['data']['attributes']['department']);
                    }
                },
            ],
        ];
    }

    public function testInvalidSortFieldReturnsError(): void
    {
        $client = static::createClient();

        DepartmentFactory::createOne();
        EmployeeFactory::createOne();

        $client->request('GET', '/api/payroll', ['sort' => 'invalidField']);

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('errors', $response);
        $this->assertStringContainsString('invalidField', $response['errors'][0]['detail'] ?? '');
    }

    public function testEmptyResultSet(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/payroll');

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    public function testFilterWithNoMatchesReturnsEmptyArray(): void
    {
        $client = static::createClient();

        $department = DepartmentFactory::createOne(['name' => 'Engineering']);
        EmployeeFactory::createOne(['name' => 'John', 'department' => $department]);

        $client->request('GET', '/api/payroll', ['filter' => ['name' => 'NonExistentName']]);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    /**
     * @dataProvider multipleFiltersDataProvider
     */
    public function testMultipleFilters(array $filters, int $expectedCount): void
    {
        $client = static::createClient();

        $dept1 = DepartmentFactory::createOne(['name' => 'Engineering', 'bonusType' => DepartmentBonusTypeEnum::FIXED_BONUS, 'bonusValue' => 500]);
        $dept2 = DepartmentFactory::createOne(['name' => 'Marketing', 'bonusType' => DepartmentBonusTypeEnum::PERCENT_BONUS, 'bonusValue' => 10]);

        EmployeeFactory::createOne(['name' => 'John', 'surname' => 'Doe', 'remunerationBase' => 10000, 'yearsOfWork' => 5, 'department' => $dept1]);
        EmployeeFactory::createOne(['name' => 'John', 'surname' => 'Smith', 'remunerationBase' => 8000, 'yearsOfWork' => 3, 'department' => $dept1]);
        EmployeeFactory::createOne(['name' => 'Jane', 'surname' => 'Doe', 'remunerationBase' => 12000, 'yearsOfWork' => 7, 'department' => $dept2]);

        $client->request('GET', '/api/payroll', ['filter' => $filters]);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertCount($expectedCount, $response);
    }

    /**
     * @return array<string, array{array<string, string>, int}>
     */
    public static function multipleFiltersDataProvider(): array
    {
        return [
            'filter by name and surname' => [
                ['name' => 'John', 'surname' => 'Doe'],
                1,
            ],
            'filter by name and department' => [
                ['name' => 'John', 'department' => 'Engineering'],
                2,
            ],
            'filter by surname and department' => [
                ['surname' => 'Doe', 'department' => 'Engineering'],
                1,
            ],
            'filter by all three fields' => [
                ['name' => 'John', 'surname' => 'Doe', 'department' => 'Engineering'],
                1,
            ],
            'filter by all three fields no match' => [
                ['name' => 'John', 'surname' => 'Doe', 'department' => 'Marketing'],
                0,
            ],
        ];
    }

    public function testResponseStructure(): void
    {
        $client = static::createClient();

        $department = DepartmentFactory::createOne([
            'name' => 'Engineering',
            'bonusType' => DepartmentBonusTypeEnum::FIXED_BONUS,
            'bonusValue' => 500,
        ]);

        EmployeeFactory::createOne([
            'name' => 'John',
            'surname' => 'Doe',
            'remunerationBase' => 10000,
            'yearsOfWork' => 5,
            'department' => $department,
        ]);

        $client->request('GET', '/api/payroll');

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertNotEmpty($response);

        $firstItem = $response[0];
        $this->assertArrayHasKey('data', $firstItem);
        $this->assertArrayHasKey('id', $firstItem['data']);
        $this->assertArrayHasKey('type', $firstItem['data']);
        $this->assertArrayHasKey('attributes', $firstItem['data']);

        $attributes = $firstItem['data']['attributes'];
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('surname', $attributes);
        $this->assertArrayHasKey('department', $attributes);
        $this->assertArrayHasKey('remunerationBase', $attributes);
        $this->assertArrayHasKey('additionAmount', $attributes);
        $this->assertArrayHasKey('bonusType', $attributes);
        $this->assertArrayHasKey('finalRemuneration', $attributes);
    }
}
