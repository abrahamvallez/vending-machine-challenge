<?php

declare(strict_types=1);

namespace App\Domain;

class VendorMachine
{
  private int $inventory = 1;
  private float $moneyInserted = 0;


  public function insertCoin(float $coin): void
  {
    $this->moneyInserted += $coin;
  }

  public function getMoneyInserted(): float
  {
    return $this->moneyInserted;
  }
  public function getInventory(): int
  {
    return $this->inventory;
  }

  public function buy(string $item): bool
  {
    if ($this->moneyInserted < 1) {
      throw new NotEnoughMoneyException('Not enough money inserted');
    }
    if ($this->inventory === 0) {
      throw new NotEnoughInventoryException('Not enough inventory');
    }
    $this->inventory--;
    return true;
  }
}
