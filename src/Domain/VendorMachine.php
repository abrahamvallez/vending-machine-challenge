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

  public function buy(string $item): int
  {
    if ($this->moneyInserted < 1) {
      throw new NotEnoughMoneyException();
    }
    if ($this->inventory === 0) {
      throw new NotEnoughInventoryException();
    }
    $this->inventory--;
    return 1;
  }
}
