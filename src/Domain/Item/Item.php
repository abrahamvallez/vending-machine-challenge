<?php

namespace App\Domain\Item;

class Item
{
  public readonly string $selector;

  public function __construct(
    public readonly SupportedItems $itemType,
    public readonly int $price
  ) {
    $this->selector = $itemType->value;
  }
}
