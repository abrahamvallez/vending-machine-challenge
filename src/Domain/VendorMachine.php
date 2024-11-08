<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Exceptions\NotEnoughInventoryException;
use App\Domain\Exceptions\NotEnoughMoneyException;

class VendorMachine
{
  private int $inventory = 1;
  private int $moneyInserted = 0;

  public function __construct(private CoinInventory $coinInventory) {}

  public function insertCoin(Coin $coin): void
  {
    $this->moneyInserted += $coin->value;
    $this->coinInventory->addCoin($coin);
  }

  public function getInventory(): int
  {
    return $this->inventory;
  }

  public function buy(string $item): Sale
  {
    $change = [];
    if ($this->inventory === 0) {
      throw new NotEnoughInventoryException('Not enough inventory');
    }
    if ($this->moneyInserted < Coin::fromValueOnCents(100)->value) {
      throw new NotEnoughMoneyException('Not enough money inserted');
    }
    if ($this->moneyInserted > Coin::fromValueOnCents(100)->value) {
      $change = $this->coinInventory->getCoinsForChange($this->moneyInserted, 100);
    }
    $this->inventory--;
    return new Sale($change, $item);
  }
}
