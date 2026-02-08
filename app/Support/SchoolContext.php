<?php

namespace App\Support;
use App\Models\School;
class SchoolContext
{
    protected static ?School $school = null;
    public static function set(?School $school): void
    {
        self::$school = $school;
    }
    public static function forget(): void
    {
        self::$school = null;
    }

    public static function school(): ?School
    {
        return self::$school;
    }
    public static function id(): ?int
    {
        return self::$school?->id;
    }
}
