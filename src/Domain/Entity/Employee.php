<?php

namespace App\Domain\Entity;

use Symfony\Component\Uid\Uuid;

class Employee
{
    /** @phpstan-ignore-next-line */
    private Uuid $id;

    private string $name;

    private string $surname;

    private int $remunerationBase;

    private ?int $yearsOfWork = null;

    private Department $department;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    public function getRemunerationBase(): int
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
