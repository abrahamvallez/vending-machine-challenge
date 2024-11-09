<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use PHPUnit\Framework\TestCase;
use App\Domain\Coin\Coin;

class CoinTest extends TestCase
{

  public function testCreate1EuroIs100Cents(): void
  {
    $this->assertEquals(100, Coin::oneEuro()->value);
  }

  public function testCreateQuarterIs25Cents(): void
  {
    $this->assertEquals(25, Coin::quarter()->value);
  }

  public function testCreateTenIs10Cents(): void
  {
    $this->assertEquals(10, Coin::ten()->value);
  }

  public function testCreateNickelIs5Cents(): void
  {
    $this->assertEquals(5, Coin::nickel()->value);
  }

  public function testCreateFromValueSetCorrectValue(): void
  {
    $this->assertEquals(1, Coin::fromValueOnCents(1)->value);
  }
}
