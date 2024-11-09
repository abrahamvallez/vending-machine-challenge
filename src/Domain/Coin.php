<?php

declare(strict_types=1);

namespace App\Domain;

use ValueError;

class Coin
{
  public function __construct(
    public readonly int $value
  ) {}

  public static function oneEuro(): self
  {
    return new self(100);
  }

  public static function quarter(): self
  {
    return new self(25);
  }

  public static function ten(): self
  {
    return new self(10);
  }

  public static function nickel(): self
  {
    return new self(5);
  }

  public static function fromValueOnCents(int $value): self
  {
    return new self($value);
  }

  public static function coinsValue(array $coins): int
  {
    return array_sum(array_map(fn(Coin $coin) => $coin->value, $coins));
  }
}
