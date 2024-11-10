<?php

namespace App\Tests;

use PHPUnit\Framework\{Attributes\Group, Attributes\DataProvider, TestCase};
use App\Domain\Coin\{CashBox, Coin};
use App\Domain\{VendorMachine, Sale};
use App\Domain\Exceptions\{NotEnoughMoneyException, NotEnoughInventoryException, NotEnoughChangeException};
use App\Domain\Item\{SupportedItems, Item};
use InvalidArgumentException;

class VendorMachineTest extends TestCase
{
  private VendorMachine $vendorMachine;

  protected function setUp(): void
  {
    $this->vendorMachine = new VendorMachine(new CashBox(), [
      SupportedItems::JUICE->value => ['item' => new Item(SupportedItems::JUICE, 100), 'quantity' => 1],
      SupportedItems::SODA->value => ['item' => new Item(SupportedItems::SODA, 150), 'quantity' => 1],
      SupportedItems::WATER->value => ['item' => new Item(SupportedItems::WATER, 65), 'quantity' => 1],
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
  public function testReturnItemWhenBuy(array $coins, SupportedItems $itemType): void
  {
    $this->insertCoinsToVendorMachine($coins);
    $sale = $this->vendorMachine->buy($itemType);
    $this->assertEquals($itemType, $sale->item->itemType);
  }

  #[Group('buy_items')]
  public static function buyItemProvider(): array
  {
    return [
      'JUICE' => [[Coin::oneEuro()], SupportedItems::JUICE],
      'SODA' => [[Coin::oneEuro(), Coin::quarter(), Coin::quarter()], SupportedItems::SODA],
      'WATER' => [[Coin::quarter(), Coin::quarter(), Coin::ten(), Coin::nickel()], SupportedItems::WATER],
    ];
  }

  #[Group('buy_items')]
  public function testItemIsRemovedFromInventaryWhenIsSold(): void
  {
    $itemInventory = $this->vendorMachine->getInventory();
    $this->assertEquals(1, $itemInventory[SupportedItems::JUICE->value]['quantity']);
    $this->vendorMachine->insertCoin(Coin::oneEuro());
    $this->vendorMachine->buy(SupportedItems::JUICE);
    $itemInventory = $this->vendorMachine->getInventory();
    $this->assertEquals(0, $itemInventory[SupportedItems::JUICE->value]['quantity']);
  }

  #[Group('buy_items')]
  #[DataProvider('notEnoughMoneyProvider')]
  public function testNotSellIfNotEnoughMoney(array $coins): void
  {
    $this->insertCoinsToVendorMachine($coins);
    $this->expectException(NotEnoughMoneyException::class);
    $this->vendorMachine->buy(SupportedItems::JUICE);
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
    $this->expectException(NotEnoughInventoryException::class, 'Not enough inventory');
    $this->vendorMachine->buy(SupportedItems::JUICE);
    $this->vendorMachine->buy(SupportedItems::JUICE);
  }

  #[Group('returning_change')]
  #[DataProvider('buyItemWithExactMoneyProvider')]
  public function testGetItemAndNoChangeWhenBuyWithExactMoney(array $coins, SupportedItems $itemType): void
  {
    $this->insertCoinsToVendorMachine($coins);
    $sale = $this->vendorMachine->buy($itemType);
    $this->assertEquals($itemType, $sale->item->itemType);
    $this->assertEquals([], $sale->change);
  }

  #[Group('returning_change')]
  public static function buyItemWithExactMoneyProvider(): array
  {
    return [
      '1 euro' => [[Coin::oneEuro()], SupportedItems::JUICE],
      '0.25 cents' => [array_fill(0, 4, Coin::quarter()), SupportedItems::JUICE],
      '0.10 cents' => [array_fill(0, 10, Coin::ten()), SupportedItems::JUICE],
      '0.05 cents' => [array_fill(0, 20, Coin::nickel()), SupportedItems::JUICE],
      '1 quarter, 1 quarter, 1 ten, 1 ten, 1 nickel, 1 quarter' => [[Coin::quarter(), Coin::quarter(), Coin::ten(), Coin::ten(), Coin::nickel(), Coin::quarter()], SupportedItems::JUICE],
      '1 quarter, 1 quarter, 1 ten, 1 ten, 1 ten, 1 ten, 1 ten' => [[Coin::quarter(), Coin::quarter(), Coin::ten(), Coin::ten(), Coin::ten(), Coin::ten(), Coin::ten()], SupportedItems::JUICE],
      '1 nickel, 1 quarter, 1 nickel, 1 nickel, 1 ten, 1 quarter, 1 quarter' => [[Coin::nickel(), Coin::quarter(), Coin::nickel(), Coin::nickel(), Coin::ten(), Coin::quarter(), Coin::quarter()], SupportedItems::JUICE],
    ];
  }

  #[Group('returning_change')]
  #[DataProvider('changeWhenBuyWithMoreMoneyProvider')]
  public function testReturnCorrectValueChangeWhenBuyWithMoreMoney(array $coins, SupportedItems $itemType, int $price): void
  {
    $this->insertCoinsToVendorMachine($coins);
    $insertedValue = Coin::coinsValue($coins);
    $expectedChangeMoneyValue = $insertedValue - $price;
    $return = $this->vendorMachine->buy($itemType);
    $changeValue = Coin::coinsValue($return->change);
    $this->assertEquals($expectedChangeMoneyValue, $changeValue);
  }

  public static function changeWhenBuyWithMoreMoneyProvider(): array
  {
    return [
      '1 quarter' => [[Coin::oneEuro(), Coin::quarter()], SupportedItems::JUICE, 100],
      '1 quarter' => [[Coin::quarter(), Coin::quarter(), Coin::quarter(), Coin::quarter(), Coin::quarter()], SupportedItems::JUICE, 100],
      '1 quarter, 1 ten' => [[Coin::oneEuro(), Coin::quarter(), Coin::ten()], SupportedItems::JUICE, 100],
      '1 quarter, 1 ten, 1 nickel' => [[Coin::oneEuro(), Coin::quarter(), Coin::ten(), Coin::nickel()], SupportedItems::JUICE, 100],
      '1 ten' => [[Coin::oneEuro(), Coin::nickel(), Coin::nickel()], SupportedItems::JUICE, 100],
    ];
  }

  public function testThrowExceptionAndNotSellWhenNoChangeAvailable(): void
  {
    $this->expectException(NotEnoughChangeException::class);
    $this->insertCoinsToVendorMachine([Coin::quarter(), ...array_fill(0, 8, Coin::ten())]);
    $this->vendorMachine->buy(SupportedItems::JUICE);
  }

  public function testCashBackReturnsMoneyInserted(): void
  {
    $this->insertCoinsToVendorMachine([Coin::oneEuro(), Coin::quarter()]);
    $change = $this->vendorMachine->cashBack();
    $this->assertEquals(125, Coin::coinsValue($change));
  }

  public function testGetChangeReturnsEmptyArrayWhenNoChangeAvailable(): void
  {
    $cashAvailable = $this->vendorMachine->getCashAvailable();
    $this->assertEquals([100 => 0, 25 => 0, 10 => 0, 5 => 0], $cashAvailable);
  }

  public function testGetChangeReturnsCoinsAvailableForChange(): void
  {
    $this->insertCoinsToVendorMachine([Coin::oneEuro(), Coin::quarter(), Coin::ten(), Coin::nickel()]);
    $cashAvailable = $this->vendorMachine->getCashAvailable();
    $this->assertEquals([100 => 1, 25 => 1, 10 => 1, 5 => 1], $cashAvailable);
  }

  public function testGetRevenueReturnsZeroWhenNoSales(): void
  {
    $revenue = $this->vendorMachine->getRevenue();
    $this->assertEquals(0, $revenue);
  }

  public function testGetRevenueWhenSales(): void
  {
    $this->insertCoinsToVendorMachine([Coin::oneEuro()]);
    $this->vendorMachine->buy(SupportedItems::JUICE);
    $this->insertCoinsToVendorMachine([Coin::oneEuro(), Coin::quarter(), Coin::quarter()]);
    $this->vendorMachine->buy(SupportedItems::SODA);
    $revenue = $this->vendorMachine->getRevenue();
    $this->assertEquals(150 + 100, $revenue);
  }

  public function testRevenueIsIncreasedOnlyWithItemPrice(): void
  {
    $this->insertCoinsToVendorMachine([Coin::oneEuro(), Coin::quarter()]);
    $this->vendorMachine->buy(SupportedItems::JUICE);
    $revenue = $this->vendorMachine->getRevenue();
    $this->assertEquals(100, $revenue);
  }

  public function testSetItemQuantity(): void
  {
    $this->vendorMachine->setItemQuantity(SupportedItems::JUICE, 10);
    $inventory = $this->vendorMachine->getInventory();
    $this->assertEquals(10, $inventory[SupportedItems::JUICE->value]['quantity']);
  }

  public function testSetItemQuantityThrowsExceptionWhenQuantityIsNegative(): void
  {
    $this->expectException(InvalidArgumentException::class);
    $this->vendorMachine->setItemQuantity(SupportedItems::JUICE, -1);
  }

  public function testSetChangeSwitchesCashBox(): void
  {
    $this->vendorMachine->setCashAvailable(new CashBox([100 => 1, 25 => 1, 10 => 1, 5 => 1]));
    $this->assertEquals([100 => 1, 25 => 1, 10 => 1, 5 => 1], $this->vendorMachine->getCashAvailable());
  }
}
