<?php

namespace App\Domain\Entity;

use App\Domain\Enum\DepartmentBonusTypeEnum;
use Symfony\Component\Uid\Uuid;

class Department
{
    /** @phpstan-ignore-next-line */
    private Uuid $id;

    private string $name;

    private DepartmentBonusTypeEnum $bonusType;

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
