<?php

namespace App\Domain\Item;

class Item
{
  public function __construct(readonly string $name, readonly int $value) {}

  public static function isSupportedItem(string $name): bool
  {
    return SupportedItems::isCorrectItemName($name);
  }
}
