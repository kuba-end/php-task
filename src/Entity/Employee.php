<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $surname = null;

    #[ORM\Column]
    private ?int $remunerationBase = null;

    #[ORM\Column]
    private ?int $yearsOfWork = null;

    #[ORM\ManyToOne(targetEntity: Department::class, inversedBy: 'employees')]
    #[ORM\JoinColumn(name: 'department_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Department $department;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    public function getRemunerationBase(): ?int
    {
        return $this->remunerationBase;
    }

    public function setRemunerationBase(int $remunerationBase): static
    {
        $this->remunerationBase = $remunerationBase;

        return $this;
    }

    public function getYearsOfWork(): ?int
    {
        return $this->yearsOfWork;
    }

    public function setYearsOfWork(int $yearsOfWork): static
    {
        $this->yearsOfWork = $yearsOfWork;

        return $this;
    }

    public function getDepartment(): Department
    {
        return $this->department;
    }

    public function setDepartment(Department $department): Employee
    {
        $this->department = $department;
        return $this;
    }
}
