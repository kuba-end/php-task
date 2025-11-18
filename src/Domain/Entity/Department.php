<?php

namespace App\Domain\Entity;

use App\Domain\Enum\DepartmentBonusTypeEnum;
use App\Infrastructure\Repository\DepartmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    /** @phpstan-ignore-next-line */
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(enumType: DepartmentBonusTypeEnum::class)]
    private DepartmentBonusTypeEnum $bonusType;

    #[ORM\Column]
    private ?int $bonusValue = null;

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

    public function getBonusType(): DepartmentBonusTypeEnum
    {
        return $this->bonusType;
    }

    public function setBonusType(DepartmentBonusTypeEnum $bonusType): static
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
