<?php

namespace App\Infrastructure\Repository;

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
    public function findAllFilteredAndSorted(array $filters = [], ?string $sort = null): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('e')
            ->from(Employee::class, 'e');

        if (isset($filters['department'])) {
            $qb->andWhere('e.department = :department')
                ->setParameter('department', $filters['department']);
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
            [$field, $direction] = $this->parseSort($sort);
            $qb->orderBy("e.$field", $direction);
        }

        return $qb->getQuery()->getResult();
    }

    private function parseSort(string $sort): array
    {
        $direction = 'ASC';

        if (str_starts_with($sort, '-')) {
            $direction = 'DESC';
            $sort = substr($sort, 1);
        }

        return [$sort, $direction];
    }
}
