<?php

namespace App\Domain;

class Item
{
  public function __construct(readonly string $name, readonly int $value) {}
}
