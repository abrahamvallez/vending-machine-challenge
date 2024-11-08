<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Exceptions\NotEnoughChangeException;
use InvalidArgumentException;
use App\Domain\SupportedCoins;

class CoinInventory
{
  private array $quantities;

  public function __construct(array $coinInventory = [])
  {
    $this->quantities = array_fill_keys(array_map(fn($supportedCoin) => $supportedCoin->value, SupportedCoins::cases()), 0);
    foreach ($coinInventory as $coinValue => $quantity) {
      if (!array_key_exists($coinValue, $this->quantities)) {
        throw new InvalidArgumentException('Coin not supported: ' . $coinValue);
      }
      $this->quantities[$coinValue] = $quantity;
    }
  }

  public function getQuantities(): array
  {
    return $this->quantities;
  }

  public function addCoin(Coin $coin): void
  {
    if (!SupportedCoins::isSupported($coin)) {
      throw new InvalidArgumentException('Coin not supported: ' . $coin->value);
    }
    $this->quantities[$coin->value]++;
  }

  public function getCoinsForChange(int $moneyInserted, int $itemPrice): array
  {
    $remainingChange = $moneyInserted - $itemPrice;
    if ($remainingChange < 0) return [];
    return $this->calculateChange($remainingChange);
  }

  private function calculateChange(int &$remainingChange): array
  {
    $change = [];
    foreach (SupportedCoins::cases() as $coin) {
      while ($remainingChange >= $coin->value && $this->quantities[$coin->value] > 0) {
        $remainingChange -= $coin->value;
        $change[] = Coin::fromValueOnCents($coin->value);
        $this->quantities[$coin->value]--;
      }
    }
    if ($remainingChange > 0) {
      throw new NotEnoughChangeException();
    }
    return $change;
  }
}
