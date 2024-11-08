<?php

declare(strict_types=1);

namespace App;

class VendorMachine
{
  private int $inventory = 1;


  public function insertCoin(int $coin): bool
  {
    return $coin === 1;
  }

  public function buy(string $item): int
  {
    $this->inventory--;
    return 1;
  }

  public function getInventory(): int
  {
    return $this->inventory;
  }
}
