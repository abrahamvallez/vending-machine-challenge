<?php

namespace App\Tests;

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

  /** @group accept_coins */

  /** @dataProvider acceptCoinProvider */
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

  public static function acceptCoinProvider(): array
  {
    return [
      '1 euro' => [Coin::oneEuro()],
      '0.25 euro' => [Coin::quarter()],
      '0.10 euro' => [Coin::ten()],
      '0.05 euro' => [Coin::nickel()],
    ];
  }

  /** @group buy_items */

  /** @dataProvider buyJuiceProvider */
  public function testGetJuiceWhenBuyExactly(array $coins): void
  {
    foreach ($coins as $coin) {
      $this->vendorMachine->insertCoin($coin);
    }
    $this->assertTrue($this->vendorMachine->buy('Juice'));
  }

  public static function buyJuiceProvider(): array
  {
    return [
      '1 euro' => [[Coin::oneEuro()]],
      '0.25 cents' => [array_fill(0, 4, Coin::quarter())],
      '0.10 cents' => [array_fill(0, 10, Coin::ten())],
      '0.05 cents' => [array_fill(0, 20, Coin::nickel())],
    ];
  }

  public function testAJuiceIsRemovedFromInventaryWhenIsSold(): void
  {
    $this->assertEquals(1, $this->vendorMachine->getInventory());
    $this->vendorMachine->insertCoin(Coin::oneEuro());
    $this->vendorMachine->buy('Juice');
    $this->assertEquals(0, $this->vendorMachine->getInventory());
  }

  /**  @dataProvider notEnoughMoneyProvider */
  public function testNotSellIfNotEnoughMoney(array $coins): void
  {
    foreach ($coins as $coin) {
      $this->vendorMachine->insertCoin($coin);
    }
    $this->expectException(NotEnoughMoneyException::class, 'Not enough money inserted');
    $this->vendorMachine->buy('Juice');
  }

  public static function notEnoughMoneyProvider(): array
  {
    return [
      'no money inserted' => [[]],
      '25 cents inserted' => [[Coin::quarter()]],
      '75 cents inserted on 25 cents' => [[Coin::quarter(), Coin::quarter(), Coin::quarter()]],
    ];
  }

  public function testNotSellIfNotEnoughInventory(): void
  {
    $this->vendorMachine->insertCoin(Coin::oneEuro());
    $this->vendorMachine->buy('Juice');
    $this->expectException(NotEnoughInventoryException::class, 'Not enough inventory');
    $this->vendorMachine->buy('Juice');
  }
}
