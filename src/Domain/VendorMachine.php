<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Exceptions\NotEnoughInventoryException;
use App\Domain\Exceptions\NotEnoughMoneyException;
use InvalidArgumentException;
use PHPUnit\Framework\TestStatus\Success;
use App\Domain\Item\SupportedItems;
use App\Domain\Coin\CashBox;
use App\Domain\Coin\Coin;
use App\Domain\Item\Item;

class VendorMachine
{
  private int $moneyInserted = 0;
  private int $revenue = 0;

  public function __construct(private CashBox $CashBox = new CashBox(), private array $itemsInventory = [
    SupportedItems::JUICE->name => 5,
    SupportedItems::SODA->name => 5,
    SupportedItems::WATER->name => 5,
  ]) {}

  public function insertCoin(Coin $coin): void
  {
    $this->moneyInserted += $coin->value;
    $this->CashBox->addCoin($coin);
  }

  public function getInventory(): array
  {
    return $this->itemsInventory;
  }

  public function buy(Item $item): Sale
  {
    if (!Item::isSupportedItem($item->selector)) {
      throw new InvalidArgumentException('Item not supported: ' . $item->selector);
    }

    $change = [];
    if ($this->itemsInventory[$item->selector] === 0) {
      throw new NotEnoughInventoryException('Not enough inventory');
    }
    if ($this->moneyInserted < $item->price) {
      throw new NotEnoughMoneyException('Not enough money inserted');
    }
    if ($this->moneyInserted > $item->price) {
      $change = $this->CashBox->getCoinsForChange($this->moneyInserted, $item->price);
    }
    $this->itemsInventory[$item->selector]--;
    $this->moneyInserted = 0;
    $this->revenue += $item->price;
    return new Sale($change, $item);
  }

  public function cashBack(): array
  {
    $change = $this->CashBox->getValueInCoins($this->moneyInserted);
    $this->moneyInserted = 0;
    return $change;
  }

  public function getCashAvailable(): array
  {
    return $this->CashBox->getCashQuantities();
  }

  public function getRevenue(): int
  {
    return $this->revenue;
  }

  public function setItemQuantity(string $itemSelector, int $quantity): void
  {
    if (!Item::isSupportedItem($itemSelector)) {
      throw new InvalidArgumentException('Item not supported: ' . $itemSelector);
    }
    if ($quantity < 0) {
      throw new InvalidArgumentException('Quantity cannot be negative');
    }
    $this->itemsInventory[$itemSelector] = $quantity;
  }

  public function setCashAvailable(CashBox $cashBox): void
  {
    $this->CashBox = $cashBox;
  }
}
