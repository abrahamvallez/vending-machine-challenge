<?php

namespace App\Tests;

use PHPUnit\Framework\{Attributes\Group, Attributes\DataProvider, TestCase};
use App\Domain\{VendorMachine, Coin, Sale, CoinInventory};
use App\Domain\Exceptions\{NotEnoughMoneyException, NotEnoughInventoryException, NotEnoughChangeException};
use App\Domain\SupportedItems;

class VendorMachineTest extends TestCase
{
  private VendorMachine $vendorMachine;

  protected function setUp(): void
  {
    $this->vendorMachine = new VendorMachine(new CoinInventory(), [
      SupportedItems::JUICE->name => 1,
      SupportedItems::SODA->name => 1,
      SupportedItems::WATER->name => 1,
    ]);
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
    $sale = $this->vendorMachine->buy(SupportedItems::JUICE->name);
    $this->assertEquals(SupportedItems::JUICE->name, $sale->item);
  }

  #[Group('buy_items')]
  public function testItemIsRemovedFromInventaryWhenIsSold(): void
  {
    $itemInventory = $this->vendorMachine->getInventory();
    $this->assertEquals(1, $itemInventory[SupportedItems::JUICE->name]);
    $this->vendorMachine->insertCoin(Coin::oneEuro());
    $this->vendorMachine->buy(SupportedItems::JUICE->name);
    $itemInventory = $this->vendorMachine->getInventory();
    $this->assertEquals(0, $itemInventory[SupportedItems::JUICE->name]);
  }

  #[Group('buy_items')]
  #[DataProvider('notEnoughMoneyProvider')]
  public function testNotSellIfNotEnoughMoney(array $coins): void
  {
    $this->insertCoinsToVendorMachine($coins);
    $this->expectException(NotEnoughMoneyException::class);
    $this->vendorMachine->buy(SupportedItems::JUICE->name);
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
    $this->vendorMachine->buy(SupportedItems::JUICE->name);
    $this->expectException(NotEnoughInventoryException::class, 'Not enough inventory');
    $this->vendorMachine->buy(SupportedItems::JUICE->name);
  }

  #[Group('returning_change')]
  #[DataProvider('buyItemWithExactMoneyProvider')]
  public function testGetItemAndNoChangeWhenBuyWithExactMoney(array $coins, string $itemName): void
  {
    $expectedSale = new Sale([], $itemName);
    $this->insertCoinsToVendorMachine($coins);
    $this->assertEquals($expectedSale, $this->vendorMachine->buy($itemName));
  }

  #[Group('returning_change')]
  public static function buyItemWithExactMoneyProvider(): array
  {
    return [
      '1 euro' => [[Coin::oneEuro()], SupportedItems::JUICE->name],
      '0.25 cents' => [array_fill(0, 4, Coin::quarter()), SupportedItems::JUICE->name],
      '0.10 cents' => [array_fill(0, 10, Coin::ten()), SupportedItems::JUICE->name],
      '0.05 cents' => [array_fill(0, 20, Coin::nickel()), SupportedItems::JUICE->name],
      '1 quarter, 1 quarter, 1 ten, 1 ten, 1 nickel, 1 quarter' => [[Coin::quarter(), Coin::quarter(), Coin::ten(), Coin::ten(), Coin::nickel(), Coin::quarter()], SupportedItems::JUICE->name],
      '1 quarter, 1 quarter, 1 ten, 1 ten, 1 ten, 1 ten, 1 ten' => [[Coin::quarter(), Coin::quarter(), Coin::ten(), Coin::ten(), Coin::ten(), Coin::ten(), Coin::ten()], SupportedItems::JUICE->name],
      '1 nickel, 1 quarter, 1 nickel, 1 nickel, 1 ten, 1 quarter, 1 quarter' => [[Coin::nickel(), Coin::quarter(), Coin::nickel(), Coin::nickel(), Coin::ten(), Coin::quarter(), Coin::quarter()], SupportedItems::JUICE->name],
    ];
  }

  #[Group('returning_change')]
  #[DataProvider('changeWhenBuyWithMoreMoneyProvider')]
  public function testReturnCorrectValueChangeWhenBuyWithMoreMoney(array $coins, string $itemName): void
  {
    $this->insertCoinsToVendorMachine($coins);
    $insertedValue = array_sum(array_map(fn(Coin $coin) => $coin->value, $coins));
    $expectedChangeMoneyValue = $insertedValue - 100;

    $return = $this->vendorMachine->buy($itemName);
    $changeValue = array_sum(array_map(fn(Coin $coin) => $coin->value, $return->change));
    $this->assertEquals($expectedChangeMoneyValue, $changeValue);
  }

  public static function changeWhenBuyWithMoreMoneyProvider(): array
  {
    return [
      '1 quarter' => [[Coin::oneEuro(), Coin::quarter()], SupportedItems::JUICE->name],
      '1 quarter' => [[Coin::quarter(), Coin::quarter(), Coin::quarter(), Coin::quarter(), Coin::quarter()], SupportedItems::JUICE->name],
      '1 quarter, 1 ten' => [[Coin::oneEuro(), Coin::quarter(), Coin::ten()], SupportedItems::JUICE->name],
      '1 quarter, 1 ten, 1 nickel' => [[Coin::oneEuro(), Coin::quarter(), Coin::ten(), Coin::nickel()], SupportedItems::JUICE->name],
      '1 ten' => [[Coin::oneEuro(), Coin::nickel(), Coin::nickel()], SupportedItems::JUICE->name],
    ];
  }

  public function testThrowExceptionAndNotSellWhenNoChangeAvailable(): void
  {
    $this->expectException(NotEnoughChangeException::class);
    $this->insertCoinsToVendorMachine([Coin::quarter(), ...array_fill(0, 8, Coin::ten())]);
    $this->vendorMachine->buy(SupportedItems::JUICE->name);
  }
}
