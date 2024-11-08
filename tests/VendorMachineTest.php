<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\VendorMachine;

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
    $this->assertCount(1, $vendorMachine->getInventory());
    $vendorMachine->insertCoin(1);
    $vendorMachine->buy('Juice');
    $this->assertCount(0, $vendorMachine->getInventory());
  }
}
