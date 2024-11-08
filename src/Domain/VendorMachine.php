<?php

declare(strict_types=1);

namespace App\Domain;

class VendorMachine
{
  private int $inventory = 1;
  private int $moneyInserted = 0;


  public function insertCoin(int $coin): bool
  {
    $this->moneyInserted += $coin;
    return $coin === 1;
  }

  public function buy(string $item): int
  {
    if ($this->moneyInserted < 1) {
      throw new NotEnoughMoneyException();
    }
    $this->inventory--;
    return 1;
  }

  public function getInventory(): int
  {
    return $this->inventory;
  }
}
