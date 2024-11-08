<?php

namespace Tests\Domain;

use PHPUnit\Framework\{Attributes\DataProvider, TestCase};
use App\Domain\{CoinInventory, Coin, SupportedCoins, Exceptions\NotEnoughChangeException};
use InvalidArgumentException;

class CoinInventoryTest extends TestCase
{
  public function testQuantitiesAreInitializedWithZero(): void
  {
    $inventory = new CoinInventory();
    $this->assertEquals([
      SupportedCoins::ONE_EURO->value => 0,
      SupportedCoins::QUARTER->value => 0,
      SupportedCoins::TEN->value => 0,
      SupportedCoins::NICKEL->value => 0,
    ], $inventory->getQuantities());
  }

  public function testCoinsNotCorrectForInitInventory(): void
  {
    $this->expectException(InvalidArgumentException::class);
    new CoinInventory([1 => 10]);
  }

  public function testAddCoinMakesItAvailableForChange(): void
  {
    $inventory = new CoinInventory();
    $inventory->addCoin(Coin::oneEuro());
    $this->assertEquals([
      SupportedCoins::ONE_EURO->value => 1,
      SupportedCoins::QUARTER->value => 0,
      SupportedCoins::TEN->value => 0,
      SupportedCoins::NICKEL->value => 0,
    ], $inventory->getQuantities());
  }

  public function testThrowExceptionWhenAddingUnsupportedCoin(): void
  {
    $inventory = new CoinInventory();
    $this->expectException(InvalidArgumentException::class);
    $inventory->addCoin(Coin::fromValueOnCents(1000));
  }

  #[DataProvider('getCoinsForChangeDataProvider')]
  public function testReturnCorrectValueInCoins(int $moneyInserted, int $itemPrice): void
  {
    $valueExpected = $moneyInserted - $itemPrice;
    $inventory = new CoinInventory([
      SupportedCoins::ONE_EURO->value => 10,
      SupportedCoins::QUARTER->value => 10,
      SupportedCoins::TEN->value => 10,
      SupportedCoins::NICKEL->value => 10,
    ]);
    $change = $inventory->getCoinsForChange($moneyInserted, $itemPrice);
    $this->assertEquals($valueExpected, array_sum(array_map(fn($coin) => $coin->value, $change)));
  }

  public static function getCoinsForChangeDataProvider(): array
  {
    return [
      [100, 10],
      [100, 50],
      [100, 100],
    ];
  }

  public function testCoinsNotCorrectForChange(): void
  {
    $inventory = new CoinInventory([
      SupportedCoins::ONE_EURO->value => 1,
      SupportedCoins::QUARTER->value => 0,
      SupportedCoins::TEN->value => 0,
      SupportedCoins::NICKEL->value => 0,
    ]);
    $this->expectException(NotEnoughChangeException::class);
    $inventory->getCoinsForChange(100, 10);
  }

  public function testNotEnoughMoneyForChange(): void
  {
    $inventory = new CoinInventory([
      SupportedCoins::ONE_EURO->value => 0,
      SupportedCoins::QUARTER->value => 0,
      SupportedCoins::TEN->value => 1,
      SupportedCoins::NICKEL->value => 0,
    ]);
    $this->expectException(NotEnoughChangeException::class);
    $inventory->getCoinsForChange(150, 100);
  }
}
