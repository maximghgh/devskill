<?php

namespace App\Support;

final class CourseDifficulty
{
    public const BEGINNER_YEAR_1 = 'beginner_year_1';
    public const BEGINNER_YEAR_2 = 'beginner_year_2';
    public const BASIC = 'basic';
    public const FUNDAMENTAL = 'fundamental';
    public const OLYMPIAD = 'olympiad';

    public const LEGACY_MIDDLE = 'middle';
    public const LEGACY_ADVANCED = 'advanced';
    public const LEGACY_MIXED = 'mixed';

    public static function actualValues(): array
    {
        return [
            self::BEGINNER_YEAR_1,
            self::BEGINNER_YEAR_2,
            self::BASIC,
            self::FUNDAMENTAL,
            self::OLYMPIAD,
        ];
    }

    public static function allowedValues(): array
    {
        return [
            ...self::actualValues(),
            self::LEGACY_MIDDLE,
            self::LEGACY_ADVANCED,
            self::LEGACY_MIXED,
        ];
    }
}
