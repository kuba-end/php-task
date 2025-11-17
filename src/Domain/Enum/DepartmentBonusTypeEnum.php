<?php

namespace App\Domain\Enum;

enum DepartmentBonusTypeEnum: string
{
    case FIXED_BONUS = "fixed_bonus";
    case PERCENT_BONUS = "percent_bonus";
}
