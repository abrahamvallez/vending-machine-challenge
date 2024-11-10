<?php

namespace Tests\Domain\Item;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Ignore;
use App\Domain\Item\Item;
use App\Domain\Item\SupportedItems;

class ItemTest extends TestCase
{
  public function testItemCreatedSetSelector(): void
  {
    $item = new Item(SupportedItems::JUICE, 100);
    $this->assertEquals(SupportedItems::JUICE->value, $item->selector);
  }
}
