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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    /**
     * @return array<Employee>
     */
    public function findAllFilteredAndSorted(array $filters, ?array $sort, SortResolver $resolver): array
    {
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

        if ($sort !== null) {
            [$field, $direction] = $sort;

            if ($resolver->isDbSortable($field)) {
                $column = $resolver->getDbSortColumn($field);
                $qb->orderBy($column, $direction);
            }
        }

        return $qb->getQuery()->getResult();
    }
}
