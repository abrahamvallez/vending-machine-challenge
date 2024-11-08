<?php

declare(strict_types=1);

namespace App\Domain;

class VendorMachine
{
  private int $inventory = 1;
  private int $moneyInserted = 0;
  private array $coinInventory = [
    100 => 0,
    25 => 0,
    10 => 0,
    5 => 0,
  ];

  public function insertCoin(Coin $coin): void
  {
    $this->moneyInserted += $coin->value;
    $this->coinInventory[$coin->value]++;
  }

  public function getCoinInventory(): array
  {
    return $this->coinInventory;
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
    if ($this->moneyInserted < Coin::fromValueOnCents(100)->value) {
      throw new NotEnoughMoneyException('Not enough money inserted');
    }
    if ($this->inventory === 0) {
      throw new NotEnoughInventoryException('Not enough inventory');
    }
    $this->inventory--;
    return true;
  }
}
