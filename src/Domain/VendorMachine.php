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
      $change = $this->calculateChange($this->moneyInserted, 100);
    }
    $this->inventory--;
    return new Sale($change, $item);
  }

  private function calculateChange(int $moneyInserted, int $itemPrice): array
  {
    $change = [];
    $remainingChange = $moneyInserted - $itemPrice;

    $coinValues = [100, 25, 10, 5];

    foreach ($coinValues as $value) {
      while ($remainingChange >= $value) {
        $change[] = Coin::fromValueOnCents($value);
        $remainingChange -= $value;
      }
    }

    return $change;
  }
}
