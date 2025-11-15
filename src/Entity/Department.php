<?php

namespace App\Entity;

use App\Repository\DepartmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    private ?string $bonusType = null;

    #[ORM\Column]
    private ?int $bonusValue = null;

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

    public function getBonusType(): ?string
    {
        return $this->bonusType;
    }

    public function setBonusType(string $bonusType): static
    {
        $this->bonusType = $bonusType;

        return $this;
    }

    public function getBonusValue(): ?int
    {
        return $this->bonusValue;
    }

    public function setBonusValue(int $bonusValue): static
    {
        $this->bonusValue = $bonusValue;

        return $this;
    }
}
