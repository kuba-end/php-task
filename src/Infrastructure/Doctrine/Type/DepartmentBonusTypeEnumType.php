<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Type;

use App\Domain\Enum\DepartmentBonusTypeEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class DepartmentBonusTypeEnumType extends Type
{
    public const NAME = 'department_bonus_type_enum';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DepartmentBonusTypeEnum) {
            return $value->value;
        }

        return (string) $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?DepartmentBonusTypeEnum
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DepartmentBonusTypeEnum) {
            return $value;
        }

        return DepartmentBonusTypeEnum::from((string) $value);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}

