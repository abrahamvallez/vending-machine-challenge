<?php

namespace App\Domain\Item;

class Item
{
  public function __construct(readonly string $selector, readonly int $price) {}

  public static function isSupportedItem(string $selector): bool
  {
    return SupportedItems::isCorrectItemName($selector);
  }
}
