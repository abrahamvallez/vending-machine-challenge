<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Domain\VendorMachine;
use App\Domain\NotEnoughMoneyException;
use App\Domain\NotEnoughInventoryException;

class VendorMachineTest extends TestCase
{
  public function testAcceptCoinOf1Euro(): void
  {
    $vendorMachine = new VendorMachine();
    $this->assertTrue($vendorMachine->insertCoin(1));
  }

  public function testGetJuiceWhenBuyExactly(): void
  {
    $vendorMachine = new VendorMachine();
    $vendorMachine->insertCoin(1);
    $this->assertEquals(1, $vendorMachine->buy('Juice'));
  }

  public function testAJuiceIsRemovedFromInventaryWhenIsSold(): void
  {
    $vendorMachine = new VendorMachine();
    $this->assertEquals(1, $vendorMachine->getInventory());
    $vendorMachine->insertCoin(1);
    $vendorMachine->buy('Juice');
    $this->assertEquals(0, $vendorMachine->getInventory());
  }

  public function testShouldNotSellIfNotEnoughMoney(): void
  {
    $vendorMachine = new VendorMachine();
    $this->expectException(NotEnoughMoneyException::class);
    $vendorMachine->buy('Juice');
  }

  public function testShouldNotSellIfNotEnoughInventory(): void
  {
    $vendorMachine = new VendorMachine();
    $this->expectException(NotEnoughInventoryException::class);
    $vendorMachine->buy('Juice');
  }
}
