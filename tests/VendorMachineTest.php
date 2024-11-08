<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Domain\VendorMachine;
use App\Domain\NotEnoughMoneyException;
use App\Domain\NotEnoughInventoryException;

class VendorMachineTest extends TestCase
{
  private VendorMachine $vendorMachine;

  protected function setUp(): void
  {
    $this->vendorMachine = new VendorMachine();
  }

  /** @group accept_coins */

  /** @dataProvider acceptCoinProvider */
  public function testAcceptCoins(float $coin): void
  {
    $this->vendorMachine->insertCoin($coin);
    $this->assertEquals($coin, $this->vendorMachine->getMoneyInserted());
  }

  public static function acceptCoinProvider(): array
  {
    return [
      '1 euro' => [1],
      '0.25 euro' => [0.25],
      '0.10 euro' => [0.10],
      '0.05 euro' => [0.05],
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
      '1 euro' => [[1]],
      '0.25 cents' => [array_fill(0, 4, 0.25)],
      '0.05 cents' => [array_fill(0, 20, 0.05)],
    ];
  }

  public function testAJuiceIsRemovedFromInventaryWhenIsSold(): void
  {
    $this->assertEquals(1, $this->vendorMachine->getInventory());
    $this->vendorMachine->insertCoin(1);
    $this->vendorMachine->buy('Juice');
    $this->assertEquals(0, $this->vendorMachine->getInventory());
  }

  /**  @dataProvider notEnoughMoneyProvider */
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
      '25 cents inserted' => [[0.25]],
      '75 cents inserted on 25 cents' => [[0.25, 0.25, 0.25]],
    ];
  }

  public function testNotSellIfNotEnoughInventory(): void
  {
    $this->vendorMachine->insertCoin(1);
    $this->vendorMachine->buy('Juice');
    $this->expectException(NotEnoughInventoryException::class);
    $this->vendorMachine->buy('Juice');
  }
}
