<?php

declare(strict_types=1);

namespace App\Domain\Item;

enum SupportedItems: string
{
  case JUICE = 'juice';
  case SODA = 'soda';
  case WATER = 'water';

  public static function getValues(): array
  {
    return array_map(fn($item) => $item->value, self::cases());
  }
}
