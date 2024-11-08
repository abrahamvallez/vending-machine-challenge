<?php

namespace App\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use App\Domain\VendorMachine;
use App\Domain\NotEnoughMoneyException;
use App\Domain\NotEnoughInventoryException;
use App\Domain\Coin;

class VendorMachineTest extends TestCase
{
  private VendorMachine $vendorMachine;

  protected function setUp(): void
  {
    $this->vendorMachine = new VendorMachine();
  }

  #[Group('accept_coins')]
  #[DataProvider('acceptCoinProvider')]
  public function testAcceptCoins(Coin $coin): void
  {
    $coinInventoryDefault = [
      Coin::oneEuro()->value => 0,
      Coin::quarter()->value => 0,
      Coin::ten()->value => 0,
      Coin::nickel()->value => 0,
    ];

    $this->vendorMachine->insertCoin($coin);
    $this->assertEquals([$coin->value => 1] + $coinInventoryDefault, $this->vendorMachine->getCoinInventory());
  }

  #[Group('accept_coins')]
  public static function acceptCoinProvider(): array
  {
    return [
      '1 euro' => [Coin::oneEuro()],
      '0.25 euro' => [Coin::quarter()],
      '0.10 euro' => [Coin::ten()],
      '0.05 euro' => [Coin::nickel()],
    ];
  }

  #[Group('accept_coins')]
  #[DataProvider('mixedCoinsProvider')]
  public function testInsertMoneySaveOnInventory(array $coins, array $expectedInventory): void
  {
    foreach ($coins as $coin) {
      $this->vendorMachine->insertCoin($coin);
    }
    $this->assertEquals($expectedInventory, $this->vendorMachine->getCoinInventory());
  }

  public static function mixedCoinsProvider(): array
  {
    return [
      '1 euro and 1 quarter' => [[Coin::oneEuro(), Coin::quarter()], [
        Coin::oneEuro()->value => 1,
        Coin::quarter()->value => 1,
        Coin::ten()->value => 0,
        Coin::nickel()->value => 0,
      ]],
      '1 euro, 1 quarter, 1 ten and 1 nickel' => [[Coin::oneEuro(), Coin::quarter(), Coin::ten(), Coin::nickel()], [
        Coin::oneEuro()->value => 1,
        Coin::quarter()->value => 1,
        Coin::ten()->value => 1,
        Coin::nickel()->value => 1,
      ]],
      '1 euro, 1 quarter, 1 ten, 1 nickel and 1 euro' => [[Coin::oneEuro(), Coin::quarter(), Coin::ten(), Coin::nickel(), Coin::oneEuro()], [
        Coin::oneEuro()->value => 2,
        Coin::quarter()->value => 1,
        Coin::ten()->value => 1,
        Coin::nickel()->value => 1,
      ]],
      '1 euro, 1 quarter, 1 ten, 1 euro, 1 euro' => [[Coin::oneEuro(), Coin::quarter(), Coin::ten(), Coin::nickel(), Coin::oneEuro(), Coin::oneEuro()], [
        Coin::oneEuro()->value => 3,
        Coin::quarter()->value => 1,
        Coin::ten()->value => 1,
        Coin::nickel()->value => 1,
      ]],
      '1 quarter, 1 quarter, 1 nickel' => [[Coin::quarter(), Coin::quarter(), Coin::nickel()], [
        Coin::quarter()->value => 2,
        Coin::nickel()->value => 1,
        Coin::ten()->value => 0,
        Coin::oneEuro()->value => 0,
      ]],
    ];
  }

  #[Group('buy_items')]
  #[DataProvider('buyJuiceProvider')]
  public function testGetJuiceWhenBuyExactly(array $coins): void
  {
    foreach ($coins as $coin) {
      $this->vendorMachine->insertCoin($coin);
    }
    $this->assertTrue($this->vendorMachine->buy('Juice'));
  }

  #[Group('buy_items')]
  public static function buyJuiceProvider(): array
  {
    return [
      '1 euro' => [[Coin::oneEuro()]],
      '0.25 cents' => [array_fill(0, 4, Coin::quarter())],
      '0.10 cents' => [array_fill(0, 10, Coin::ten())],
      '0.05 cents' => [array_fill(0, 20, Coin::nickel())],
    ];
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
    foreach ($coins as $coin) {
      $this->vendorMachine->insertCoin($coin);
    }

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
}