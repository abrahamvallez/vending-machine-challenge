<?php

declare(strict_types=1);

namespace App\Domain\Coin;

use App\Domain\Exceptions\NotEnoughChangeException;
use InvalidArgumentException;
use App\Domain\Coin\SupportedCoins;

class CashBox
{
  private array $cashQuantities;

  public function __construct(array $cashQuantities = [])
  {
    $this->setCashQuantitiesTo0();
    $this->setCashQuantitiesFrom(
      $cashQuantities
    );
  }

  public function getCashQuantities(): array
  {
    return $this->cashQuantities;
  }

  public function addCoin(Coin $coin): void
  {
    if (!SupportedCoins::isSupported($coin)) {
      throw new InvalidArgumentException('Coin not supported: ' . $coin->value);
    }
    $this->cashQuantities[$coin->value]++;
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

  private function setCashQuantitiesTo0(): void
  {
    $this->cashQuantities = array_fill_keys(
      array_map(
        fn($supportedCoin) => $supportedCoin->value,
        SupportedCoins::cases()
      ),
      0
    );
  }

  private function setCashQuantitiesFrom(
    array $cashQuantities
  ): void {
    foreach (
      $cashQuantities
      as $coinValue => $quantity
    ) {
      $coin = Coin::fromValueOnCents($coinValue);
      if (!SupportedCoins::isSupported($coin)) {
        throw new InvalidArgumentException('Coin not supported: ' . $coinValue);
      }
      $this->cashQuantities[$coin->value] = $quantity;
    }
  }

  private function calculateChange(int $value): array
  {
    $change = [];
    foreach (SupportedCoins::cases() as $coin) {
      while ($value >= $coin->value && $this->cashQuantities[$coin->value] > 0) {
        $value -= $coin->value;
        $change[] = Coin::fromValueOnCents($coin->value);
        $this->cashQuantities[$coin->value]--;
      }
    }
    if ($value > 0) {
      throw new NotEnoughChangeException();
    }
    return $change;
  }
}
