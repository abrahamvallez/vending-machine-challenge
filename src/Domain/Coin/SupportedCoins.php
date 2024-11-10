<?php

declare(strict_types=1);

namespace App\Domain\Coin;

enum SupportedCoins: int
{
    case ONE_EURO = 100;
    case QUARTER = 25;
    case TEN = 10;
    case NICKEL = 5;

    public static function getValues(): array
    {
        return array_map(fn ($c) => $c->value, self::cases());
    }
}
