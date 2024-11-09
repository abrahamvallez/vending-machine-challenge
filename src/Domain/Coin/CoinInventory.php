<?php

declare(strict_types=1);

namespace App\Domain\Coin;

use App\Domain\Exceptions\NotEnoughChangeException;
use InvalidArgumentException;
use App\Domain\Coin\SupportedCoins;

class CoinInventory
{
  private array $quantities;

  public function __construct(array $coinInventory = [])
  {
    $this->setQuantitiesTo0();
    $this->setQuantitiesFrom($coinInventory);
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

  public function getValueInCoins(int $value): array
  {
    return $this->calculateChange($value);
  }

  private function setQuantitiesTo0(): void
  {
    $this->quantities = array_fill_keys(
      array_map(
        fn($supportedCoin) => $supportedCoin->value,
        SupportedCoins::cases()
      ),
      0
    );
  }

  private function setQuantitiesFrom(array $coinInventory): void
  {
    foreach ($coinInventory as $coinValue => $quantity) {
      $coin = Coin::fromValueOnCents($coinValue);
      if (!SupportedCoins::isSupported($coin)) {
        throw new InvalidArgumentException('Coin not supported: ' . $coinValue);
      }
      $this->quantities[$coin->value] = $quantity;
    }
  }

  private function calculateChange(int $value): array
  {
    $change = [];
    foreach (SupportedCoins::cases() as $coin) {
      while ($value >= $coin->value && $this->quantities[$coin->value] > 0) {
        $value -= $coin->value;
        $change[] = Coin::fromValueOnCents($coin->value);
        $this->quantities[$coin->value]--;
      }
    }
    if ($value > 0) {
      throw new NotEnoughChangeException();
    }
    return $change;
  }
}
