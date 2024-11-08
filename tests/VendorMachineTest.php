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

  // @group accept coins //
  public function testAcceptCoinOf1Euro(): void
  {
    $this->vendorMachine->insertCoin(1);
    $this->assertEquals(1, $this->vendorMachine->getMoneyInserted());
  }

  public function testAcceptCoinsOf25cents(): void
  {
    $this->vendorMachine->insertCoin(0.25);
    $this->assertEquals(0.25, $this->vendorMachine->getMoneyInserted());
  }

  // @group buy items //

  public function testGetJuiceWhenBuyExactly(): void
  {
    $this->vendorMachine->insertCoin(1);
    $this->assertTrue($this->vendorMachine->buy('Juice'));
  }

  public function testGet1JuiceWhenBuyExactlyWith25cents(): void
  {
    $this->vendorMachine->insertCoin(0.25);
    $this->vendorMachine->insertCoin(0.25);
    $this->vendorMachine->insertCoin(0.25);
    $this->vendorMachine->insertCoin(0.25);

    $this->assertTrue($this->vendorMachine->buy('Juice'));
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
