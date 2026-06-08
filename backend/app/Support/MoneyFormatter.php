<?php

namespace App\Support;

class MoneyFormatter
{
    public static function format(float|int|string|null $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
