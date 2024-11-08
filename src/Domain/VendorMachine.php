<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Exceptions\NotEnoughChangeException;
use App\Domain\Exceptions\NotEnoughInventoryException;
use App\Domain\Exceptions\NotEnoughMoneyException;

class VendorMachine
{
  private int $inventory = 1;
  private int $moneyInserted = 0;

  public function __construct(private array $coinInventory = [
    100 => 0,
    25 => 0,
    10 => 0,
    5 => 0,
  ]) {}

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
      while ($remainingChange >= $value && $this->coinInventory[$value] > 0) {
        $change[] = Coin::fromValueOnCents($value);
        $remainingChange -= $value;
        $this->coinInventory[$value]--;
      }
    }

    if ($remainingChange > 0) {
      throw new NotEnoughChangeException('Not enough change available');
    }

    return $change;
  }
}
