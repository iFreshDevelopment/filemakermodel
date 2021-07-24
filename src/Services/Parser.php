<?php

namespace Ifresh\FilemakerModel\Services;

use Carbon\Carbon;

class Parser
{
    public static function parseAsInteger($value)
    {
        return intval($value);
    }

    public static function parseAsDate($value)
    {
        return Carbon::parse($value);
    }

    public static function parseAsBoolean($value)
    {
        return boolval($value);
    }

    public static function parseAsString($value)
    {
        return strval($value);
    }

    public static function parseAsFloat($value)
    {
        return floatval($value);
    }
}
