<?php

namespace App\Tests\Domain\Item;

use App\Domain\Item\ItemInventory;
use App\Domain\Item\Item;
use App\Domain\Item\SupportedItems;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class ItemInventoryTest extends TestCase
{
  private ItemInventory $itemInventory;

  public function setUp(): void
  {
    $this->itemInventory = new ItemInventory();
  }

  public function testReturnTheQuantityOfAItem(): void
  {
    $this->itemInventory->setItem(new Item(SupportedItems::JUICE, 100), 5);
    $this->assertEquals(5, $this->itemInventory->getItemQuantity(SupportedItems::JUICE));
  }

  public function testReturnsZeroIfItemDoesNotExist(): void
  {
    $this->assertEquals(0, $this->itemInventory->getItemQuantity(SupportedItems::JUICE));
  }

  public function testReturnItem(): void
  {
    $this->itemInventory->setItem(new Item(SupportedItems::JUICE, 100), 5);
    $this->assertEquals(new Item(SupportedItems::JUICE, 100), $this->itemInventory->getItem(SupportedItems::JUICE));
  }

  public function testReturnItemThrowsExceptionIfItemDoesNotExist(): void
  {
    $this->expectException(InvalidArgumentException::class);
    $this->itemInventory->getItem(SupportedItems::JUICE);
  }

  public function testSetNewItemWhenItDoesNotExist(): void
  {
    $this->assertEquals(0, $this->itemInventory->getItemQuantity(SupportedItems::JUICE));
    $this->itemInventory->setItem(new Item(SupportedItems::JUICE, 100), 5);
    $this->assertEquals(5, $this->itemInventory->getItemQuantity(SupportedItems::JUICE));
    $this->assertEquals(SupportedItems::JUICE->value, $this->itemInventory->getItem(SupportedItems::JUICE)->selector);
  }

  public function testSetItemWhenItExists(): void
  {
    $this->itemInventory->setItem(new Item(SupportedItems::JUICE, 100), 5);
    $this->itemInventory->setItem(new Item(SupportedItems::JUICE, 100), 10);
    $this->assertEquals(10, $this->itemInventory->getItemQuantity(SupportedItems::JUICE));
  }

  public function testSetItemThrowsExceptionWhenQuantityIsNegative(): void
  {
    $this->expectException(InvalidArgumentException::class);
    $this->itemInventory->setItem(new Item(SupportedItems::JUICE, 100), -1);
  }

  public function testRemoveOneItem(): void
  {
    $this->itemInventory->setItem(new Item(SupportedItems::JUICE, 100), 5);
    $this->itemInventory->removeOneItem(SupportedItems::JUICE);
    $this->assertEquals(4, $this->itemInventory->getItemQuantity(SupportedItems::JUICE));
  }

  public function testReturnTrueIfItemIsAvailable(): void
  {
    $this->itemInventory->setItem(new Item(SupportedItems::JUICE, 100), 5);
    $this->assertTrue($this->itemInventory->isItemAvailable(SupportedItems::JUICE));
  }

  public function testReturnFalseIfItemDoesNotExist(): void
  {
    $this->assertFalse($this->itemInventory->isItemAvailable(SupportedItems::JUICE));
  }

  public function testReturnFalseIfItemIsNotAvailable(): void
  {
    $this->itemInventory->setItem(new Item(SupportedItems::JUICE, 100), 0);
    $this->assertFalse($this->itemInventory->isItemAvailable(SupportedItems::JUICE));
  }

  public function testUpdateItemQuantitySetQuantity(): void
  {
    $this->itemInventory->setItem(new Item(SupportedItems::JUICE, 100), 1);
    $this->itemInventory->updateItemQuantity(SupportedItems::JUICE, 5);
    $this->assertEquals(5, $this->itemInventory->getItemQuantity(SupportedItems::JUICE));
  }

  public function testUpdateItemQuantityThrowsExceptionIfQuantityIsNegative(): void
  {
    $this->expectException(InvalidArgumentException::class);
    $this->itemInventory->updateItemQuantity(SupportedItems::JUICE, -1);
  }

  public function testUpdateItemQuantityThrowsExceptionIfItemDoesNotExist(): void
  {
    $this->expectException(InvalidArgumentException::class);
    $this->itemInventory->updateItemQuantity(SupportedItems::JUICE, 1);
  }
}
