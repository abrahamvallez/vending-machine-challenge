<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Exceptions\NotEnoughInventoryException;
use App\Domain\Exceptions\NotEnoughMoneyException;

class VendorMachine
{
  private int $moneyInserted = 0;

  public function __construct(private CoinInventory $coinInventory = new CoinInventory(), private array $itemsInventory = [
    SupportedItems::JUICE->name => 5,
    SupportedItems::SODA->name => 5,
    SupportedItems::WATER->name => 5,
  ]) {}

  public function insertCoin(Coin $coin): void
  {
    $this->moneyInserted += $coin->value;
    $this->coinInventory->addCoin($coin);
  }

  public function getInventory(): array
  {
    return $this->itemsInventory;
  }

  public function buy(string $item): Sale
  {
    $change = [];
    if ($this->itemsInventory[$item] === 0) {
      throw new NotEnoughInventoryException('Not enough inventory');
    }
    if ($this->moneyInserted < Coin::fromValueOnCents(100)->value) {
      throw new NotEnoughMoneyException('Not enough money inserted');
    }
    if ($this->moneyInserted > Coin::fromValueOnCents(100)->value) {
      $change = $this->coinInventory->getCoinsForChange($this->moneyInserted, 100);
    }
    $this->itemsInventory[$item]--;
    return new Sale($change, $item);
  }
}
