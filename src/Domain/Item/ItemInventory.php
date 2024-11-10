<?php

namespace App\Domain\Item;

use InvalidArgumentException;

class ItemInventory
{
  private array $inventory = [];

  public function __construct() {}

  public function setItem(Item $item, int $quantity): void
  {
    if ($quantity < 0) {
      throw new InvalidArgumentException('Quantity cannot be negative');
    }
    $this->inventory[$item->selector] = ['item' => $item, 'quantity' => $quantity];
  }

  public function getItem(SupportedItems $itemType): Item
  {
    if (!isset($this->inventory[$itemType->value])) {
      throw new InvalidArgumentException('Item not found');
    }
    return $this->inventory[$itemType->value]['item'];
  }

  public function getItemQuantity(SupportedItems $itemType): int
  {
    return isset($this->inventory[$itemType->value]) ? $this->inventory[$itemType->value]['quantity'] : 0;
  }

  public function removeOneItem(SupportedItems $itemType): void
  {
    $this->inventory[$itemType->value]['quantity']--;
  }

  public function isItemAvailable(SupportedItems $itemType): bool
  {
    return isset($this->inventory[$itemType->value]) && $this->inventory[$itemType->value]['quantity'] > 0;
  }

  public function updateItemQuantity(SupportedItems $itemType, int $quantity): void
  {
    if ($quantity < 0) {
      throw new InvalidArgumentException('Quantity cannot be negative');
    }
    if (!isset($this->inventory[$itemType->value])) {
      throw new InvalidArgumentException('Item not found');
    }
    $this->inventory[$itemType->value]['quantity'] = $quantity;
  }
}
