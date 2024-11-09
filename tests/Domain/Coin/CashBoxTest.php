<?php

namespace Tests\Domain;

use PHPUnit\Framework\{Attributes\DataProvider, TestCase};
use App\Domain\Exceptions\NotEnoughChangeException;
use App\Domain\Coin\{SupportedCoins, Coin, CashBox};
use InvalidArgumentException;

class CashBoxTest extends TestCase
{
  public function testQuantitiesAreInitializedWithZero(): void
  {
    $cashBox = new CashBox();
    $this->assertEquals([
      SupportedCoins::ONE_EURO->value => 0,
      SupportedCoins::QUARTER->value => 0,
      SupportedCoins::TEN->value => 0,
      SupportedCoins::NICKEL->value => 0,
    ], $cashBox->getCashQuantities());
  }

  public function testAddCoinMakesItAvailableForChange(): void
  {
    $cashBox = new CashBox();
    $cashBox->addCoin(Coin::oneEuro());
    $this->assertEquals([
      SupportedCoins::ONE_EURO->value => 1,
      SupportedCoins::QUARTER->value => 0,
      SupportedCoins::TEN->value => 0,
      SupportedCoins::NICKEL->value => 0,
    ], $cashBox->getCashQuantities());
  }

  #[DataProvider('getCoinsForChangeDataProvider')]
  public function testReturnCorrectValueInCoins(int $moneyInserted, int $itemPrice): void
  {
    $valueExpected = $moneyInserted - $itemPrice;
    $inventory = new CashBox([
      SupportedCoins::ONE_EURO->value => 10,
      SupportedCoins::QUARTER->value => 10,
      SupportedCoins::TEN->value => 10,
      SupportedCoins::NICKEL->value => 10,
    ]);
    $change = $inventory->getCoinsForChange($moneyInserted, $itemPrice);
    $this->assertEquals($valueExpected, Coin::coinsValue($change));
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
    $inventory = new CashBox([
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
    $inventory = new CashBox([
      SupportedCoins::ONE_EURO->value => 0,
      SupportedCoins::QUARTER->value => 0,
      SupportedCoins::TEN->value => 1,
      SupportedCoins::NICKEL->value => 0,
    ]);
    $this->expectException(NotEnoughChangeException::class);
    $inventory->getCoinsForChange(150, 100);
  }

  public function testGetValueInCoinsReturnsCoinsWithCorrectValue(): void
  {
    $inventory = new CashBox([
      SupportedCoins::ONE_EURO->value => 1,
      SupportedCoins::QUARTER->value => 1,
    ]);
    $change = $inventory->getValueInCoins(125);
    $this->assertEquals([
      Coin::oneEuro(),
      Coin::quarter(),
    ], $change);
  }
}
