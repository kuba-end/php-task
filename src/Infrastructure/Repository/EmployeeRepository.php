<?php

namespace App\Infrastructure\Repository;

use App\Application\Payroll\Query\SortResolver;
use App\Domain\Entity\Employee;
use App\Domain\Repository\EmployeeRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employee>
 */
class EmployeeRepository extends ServiceEntityRepository implements EmployeeRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        public readonly SortResolver $sortResolver,
    ) {
        parent::__construct($registry, Employee::class);
    }

    public function findAllFilteredAndSorted(
        array $filters = [],
        ?string $sortField = null,
        ?string $direction = null,
    ): array {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('e', 'd')
            ->from(Employee::class, 'e')
            ->leftJoin('e.department', 'd');

        if (isset($filters['department'])) {
            $qb->andWhere('d.name = :departmentName')
                ->setParameter('departmentName', $filters['department']);
        }

        if (isset($filters['surname'])) {
            $qb->andWhere('e.surname = :surname')
                ->setParameter('surname', $filters['surname']);
        }

        if (isset($filters['name'])) {
            $qb->andWhere('e.name = :name')
                ->setParameter('name', $filters['name']);
        }

        if (
            null !== $sortField
            && $this->sortResolver->isDbSortable($sortField)
            && $this->sortResolver->getDbSortColumn($sortField)
        ) {
            $qb->orderBy($this->sortResolver->getDbSortColumn($sortField), $direction);
        }

        /** @var Employee[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }
}
