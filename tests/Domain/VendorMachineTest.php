<?php

namespace App\Tests;

use PHPUnit\Framework\{Attributes\Group, Attributes\DataProvider, TestCase};
use App\Domain\{VendorMachine, Coin, Sale, CoinInventory};
use App\Domain\Exceptions\{NotEnoughMoneyException, NotEnoughInventoryException, NotEnoughChangeException};
use App\Domain\SupportedItems;
use App\Domain\Item;
use InvalidArgumentException;

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
  #[DataProvider('buyItemProvider')]
  public function testReturnItemWhenBuy(array $coins, Item $item): void
  {
    $this->insertCoinsToVendorMachine($coins);
    $sale = $this->vendorMachine->buy($item);
    $this->assertEquals($item->name, $sale->item->name);
  }

  #[Group('buy_items')]
  public static function buyItemProvider(): array
  {
    return [
      'JUICE' => [[Coin::oneEuro()], new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value)],
      'SODA' => [[Coin::oneEuro(), Coin::quarter(), Coin::quarter()], new Item(SupportedItems::SODA->name, SupportedItems::SODA->value)],
      'WATER' => [[Coin::quarter(), Coin::quarter(), Coin::ten(), Coin::nickel()], new Item(SupportedItems::WATER->name, SupportedItems::WATER->value)],
    ];
  }

  #[Group('buy_items')]
  public function testItemIsRemovedFromInventaryWhenIsSold(): void
  {
    $itemInventory = $this->vendorMachine->getInventory();
    $this->assertEquals(1, $itemInventory[SupportedItems::JUICE->name]);
    $this->vendorMachine->insertCoin(Coin::oneEuro());
    $item = new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value);
    $this->vendorMachine->buy($item);
    $itemInventory = $this->vendorMachine->getInventory();
    $this->assertEquals(0, $itemInventory[SupportedItems::JUICE->name]);
  }

  #[Group('buy_items')]
  public function testThrowExceptionWhenItemIsSupported(): void
  {
    $item = new Item('not supported item', 1);
    $this->expectException(InvalidArgumentException::class);
    $this->vendorMachine->buy($item);
  }

  #[Group('buy_items')]
  #[DataProvider('notEnoughMoneyProvider')]
  public function testNotSellIfNotEnoughMoney(array $coins): void
  {
    $this->insertCoinsToVendorMachine($coins);
    $item = new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value);

    $this->expectException(NotEnoughMoneyException::class);
    $this->vendorMachine->buy($item);
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
    $item = new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value);
    $this->expectException(NotEnoughInventoryException::class, 'Not enough inventory');
    $this->vendorMachine->buy($item);
    $this->vendorMachine->buy($item);
  }

  #[Group('returning_change')]
  #[DataProvider('buyItemWithExactMoneyProvider')]
  public function testGetItemAndNoChangeWhenBuyWithExactMoney(array $coins, Item $item): void
  {
    $expectedSale = new Sale([], $item);
    $this->insertCoinsToVendorMachine($coins);
    $this->assertEquals($expectedSale, $this->vendorMachine->buy($item));
  }

  #[Group('returning_change')]
  public static function buyItemWithExactMoneyProvider(): array
  {
    return [
      '1 euro' => [[Coin::oneEuro()], new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value)],
      '0.25 cents' => [array_fill(0, 4, Coin::quarter()), new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value)],
      '0.10 cents' => [array_fill(0, 10, Coin::ten()), new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value)],
      '0.05 cents' => [array_fill(0, 20, Coin::nickel()), new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value)],
      '1 quarter, 1 quarter, 1 ten, 1 ten, 1 nickel, 1 quarter' => [[Coin::quarter(), Coin::quarter(), Coin::ten(), Coin::ten(), Coin::nickel(), Coin::quarter()], new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value)],
      '1 quarter, 1 quarter, 1 ten, 1 ten, 1 ten, 1 ten, 1 ten' => [[Coin::quarter(), Coin::quarter(), Coin::ten(), Coin::ten(), Coin::ten(), Coin::ten(), Coin::ten()], new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value)],
      '1 nickel, 1 quarter, 1 nickel, 1 nickel, 1 ten, 1 quarter, 1 quarter' => [[Coin::nickel(), Coin::quarter(), Coin::nickel(), Coin::nickel(), Coin::ten(), Coin::quarter(), Coin::quarter()], new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value)],
    ];
  }

  #[Group('returning_change')]
  #[DataProvider('changeWhenBuyWithMoreMoneyProvider')]
  public function testReturnCorrectValueChangeWhenBuyWithMoreMoney(array $coins, Item $item): void
  {
    $this->insertCoinsToVendorMachine($coins);
    $insertedValue = array_sum(array_map(fn(Coin $coin) => $coin->value, $coins));
    $expectedChangeMoneyValue = $insertedValue - $item->value;
    $return = $this->vendorMachine->buy($item);
    $changeValue = array_sum(array_map(fn(Coin $coin) => $coin->value, $return->change));
    $this->assertEquals($expectedChangeMoneyValue, $changeValue);
  }

  public static function changeWhenBuyWithMoreMoneyProvider(): array
  {
    return [
      '1 quarter' => [[Coin::oneEuro(), Coin::quarter()], new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value)],
      '1 quarter' => [[Coin::quarter(), Coin::quarter(), Coin::quarter(), Coin::quarter(), Coin::quarter()], new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value)],
      '1 quarter, 1 ten' => [[Coin::oneEuro(), Coin::quarter(), Coin::ten()], new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value)],
      '1 quarter, 1 ten, 1 nickel' => [[Coin::oneEuro(), Coin::quarter(), Coin::ten(), Coin::nickel()], new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value)],
      '1 ten' => [[Coin::oneEuro(), Coin::nickel(), Coin::nickel()], new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value)],
    ];
  }

  public function testThrowExceptionAndNotSellWhenNoChangeAvailable(): void
  {
    $this->expectException(NotEnoughChangeException::class);
    $this->insertCoinsToVendorMachine([Coin::quarter(), ...array_fill(0, 8, Coin::ten())]);
    $item = new Item(SupportedItems::JUICE->name, SupportedItems::JUICE->value);
    $this->vendorMachine->buy($item);
  }
}
