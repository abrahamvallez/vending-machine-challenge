<?php

declare(strict_types=1);

namespace App;

enum SupportedItems: int
{
  case JUICE = 100;
  case SODA = 150;
  case WATER = 65;

  public static function isCorrectItemName(string $itemName): bool
  {
    return in_array($itemName, array_map(fn($item) => $item->name, self::cases()), true);
  }
}
