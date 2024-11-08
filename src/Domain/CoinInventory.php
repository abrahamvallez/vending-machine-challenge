<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Exceptions\NotEnoughChangeException;
use InvalidArgumentException;

class CoinInventory
{
  private array $quantities;

  public function __construct(array $coinInventory = [])
  {
    $supportedCoins = SupportedCoins::cases();
    $this->quantities = array_fill_keys(array_map(fn($supportedCoin) => $supportedCoin->value, $supportedCoins), 0);
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
    if (!array_key_exists($coin->value, $this->quantities)) {
      throw new InvalidArgumentException('Coin not supported: ' . $coin->value);
    }

    $this->quantities[$coin->value]++;
  }

  public function getCoinsForChange(int $moneyInserted, int $itemPrice): array
  {
    $change = [];
    $remainingChange = $moneyInserted - $itemPrice;
    if ($remainingChange < 0) return [];

    $coins = SupportedCoins::cases();
    foreach ($coins as $coin) {
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

enum SupportedCoins: int
{
  case ONE_EURO = 100;
  case QUARTER = 25;
  case TEN = 10;
  case NICKEL = 5;
}
