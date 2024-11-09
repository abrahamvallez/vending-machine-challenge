<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use PHPUnit\Framework\TestCase;
use App\Domain\Coin\Coin;

class CoinTest extends TestCase
{

  public function testCreate1EuroIs100Cents(): void
  {
    $this->assertEquals(100, Coin::oneEuro()->getValueInCents());
  }

  public function testCreateQuarterIs25Cents(): void
  {
    $this->assertEquals(25, Coin::quarter()->getValueInCents());
  }

  public function testCreateTenIs10Cents(): void
  {
    $this->assertEquals(10, Coin::ten()->getValueInCents());
  }

  public function testCreateNickelIs5Cents(): void
  {
    $this->assertEquals(5, Coin::nickel()->getValueInCents());
  }

  public function testSumValuesOfCoins(): void
  {
    $this->assertEquals(115, Coin::coinsValue([Coin::oneEuro(), Coin::ten(), Coin::nickel()]));
  }
}
