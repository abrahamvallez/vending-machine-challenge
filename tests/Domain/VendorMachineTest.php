<?php

namespace App\Tests;

use PHPUnit\Framework\{Attributes\Group, Attributes\DataProvider, TestCase};
use App\Domain\{VendorMachine, Coin, Sale, CoinInventory};
use App\Domain\Exceptions\{NotEnoughMoneyException, NotEnoughInventoryException, NotEnoughChangeException};

class VendorMachineTest extends TestCase
{
  private VendorMachine $vendorMachine;

  protected function setUp(): void
  {
    $this->vendorMachine = new VendorMachine(new CoinInventory());
  }

  private function insertCoinsToVendorMachine(array $coins): void
  {
    foreach ($coins as $coin) {
      $this->vendorMachine->insertCoin($coin);
    }
  }

  #[Group('buy_items')]
  public function testReturnItemWhenBuy(): void
  {
    $this->vendorMachine->insertCoin(Coin::oneEuro());
    $sale = $this->vendorMachine->buy('Juice');
    $this->assertEquals('Juice', $sale->item);
  }

  #[Group('buy_items')]
  public function testAJuiceIsRemovedFromInventaryWhenIsSold(): void
  {
    $this->assertEquals(1, $this->vendorMachine->getInventory());
    $this->vendorMachine->insertCoin(Coin::oneEuro());
    $this->vendorMachine->buy('Juice');
    $this->assertEquals(0, $this->vendorMachine->getInventory());
  }

  #[Group('buy_items')]
  #[DataProvider('notEnoughMoneyProvider')]
  public function testNotSellIfNotEnoughMoney(array $coins): void
  {
    $this->insertCoinsToVendorMachine($coins);
    $this->expectException(NotEnoughMoneyException::class);
    $this->vendorMachine->buy('Juice');
  }

  public static function notEnoughMoneyProvider(): array
  {
    return [
      'no money inserted' => [[]],
      '25 cents inserted' => [[Coin::quarter()]],
      '75 cents inserted' => [
        [Coin::quarter(), Coin::quarter(), Coin::quarter()]
      ],
    ];
  }

  #[Group('buy_items')]
  public function testNotSellIfNotEnoughInventory(): void
  {
    $this->vendorMachine->insertCoin(Coin::oneEuro());
    $this->vendorMachine->buy('Juice');
    $this->expectException(NotEnoughInventoryException::class, 'Not enough inventory');
    $this->vendorMachine->buy('Juice');
  }

  #[Group('returning_change')]
  #[DataProvider('buyJuiceWithExactMoneyProvider')]
  public function testGetJuiceAndNoChangeWhenBuyWithExactMoney(array $coins): void
  {
    $expectedSale = new Sale([], 'Juice');
    $this->insertCoinsToVendorMachine($coins);
    $this->assertEquals($expectedSale, $this->vendorMachine->buy('Juice'));
  }

  #[Group('returning_change')]
  public static function buyJuiceWithExactMoneyProvider(): array
  {
    return [
      '1 euro' => [[Coin::oneEuro()]],
      '0.25 cents' => [array_fill(0, 4, Coin::quarter())],
      '0.10 cents' => [array_fill(0, 10, Coin::ten())],
      '0.05 cents' => [array_fill(0, 20, Coin::nickel())],
      '1 quarter, 1 quarter, 1 ten, 1 ten, 1 nickel, 1 quarter' => [[Coin::quarter(), Coin::quarter(), Coin::ten(), Coin::ten(), Coin::nickel(), Coin::quarter()]],
      '1 quarter, 1 quarter, 1 ten, 1 ten, 1 ten, 1 ten, 1 ten' => [[Coin::quarter(), Coin::quarter(), Coin::ten(), Coin::ten(), Coin::ten(), Coin::ten(), Coin::ten()]],
      '1 nickel, 1 quarter, 1 nickel, 1 nickel, 1 ten, 1 quarter, 1 quarter' => [[Coin::nickel(), Coin::quarter(), Coin::nickel(), Coin::nickel(), Coin::ten(), Coin::quarter(), Coin::quarter()]],
    ];
  }

  #[Group('returning_change')]
  #[DataProvider('changeWhenBuyWithMoreMoneyProvider')]
  public function testReturnCorrectValueChangeWhenBuyWithMoreMoney(array $coins): void
  {
    $this->insertCoinsToVendorMachine($coins);
    $insertedValue = array_sum(array_map(fn(Coin $coin) => $coin->value, $coins));
    $expectedChangeMoneyValue = $insertedValue - 100;

    $return = $this->vendorMachine->buy('Juice');
    $changeValue = array_sum(array_map(fn(Coin $coin) => $coin->value, $return->change));
    $this->assertEquals($expectedChangeMoneyValue, $changeValue);
  }

  public static function changeWhenBuyWithMoreMoneyProvider(): array
  {
    return [
      '1 quarter' => [[Coin::oneEuro(), Coin::quarter()]],
      '1 quarter' => [[Coin::quarter(), Coin::quarter(), Coin::quarter(), Coin::quarter(), Coin::quarter()]],
      '1 quarter, 1 ten' => [[Coin::oneEuro(), Coin::quarter(), Coin::ten()]],
      '1 quarter, 1 ten, 1 nickel' => [[Coin::oneEuro(), Coin::quarter(), Coin::ten(), Coin::nickel()]],
      '1 ten' => [[Coin::oneEuro(), Coin::nickel(), Coin::nickel()]],
    ];
  }

  public function testThrowExceptionAndNotSellWhenNoChangeAvailable(): void
  {
    $this->expectException(NotEnoughChangeException::class);
    $this->insertCoinsToVendorMachine([Coin::quarter(), ...array_fill(0, 8, Coin::ten())]);
    $this->vendorMachine->buy('Juice');
  }
}
