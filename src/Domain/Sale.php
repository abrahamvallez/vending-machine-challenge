<?php

namespace App\Domain;

class Sale
{
  public function __construct(
    public readonly array $change,
    public readonly Item $item,
  ) {}
}
