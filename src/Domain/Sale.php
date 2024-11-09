<?php

namespace App\Domain;

use App\Domain\Item\Item;

class Sale
{
  public function __construct(
    public readonly array $change,
    public readonly Item $item,
  ) {}
}
