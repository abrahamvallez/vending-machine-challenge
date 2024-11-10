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
use App\Domain\Item\ItemInventory;

class VendorMachine
{
  private int $moneyInserted = 0;
  private int $revenue = 0;

  public function __construct(private CashBox $cashBox = new CashBox(), private ItemInventory $itemsInventory = new ItemInventory()) {}

  public function insertCoin(Coin $coin): void
  {
    $this->moneyInserted += $coin->getValueInCents();
    $this->cashBox->addCoin($coin);
  }

  public function getInventory(): ItemInventory
  {
    return $this->itemsInventory;
  }

  public function buy(SupportedItems $itemType): Sale
  {
    $change = [];
    $itemToSell = $this->itemsInventory->getItem($itemType);
    if (!$this->itemsInventory->isItemAvailable($itemType)) {
      throw new NotEnoughInventoryException('Not enough inventory');
    }

    if ($this->moneyInserted < $itemToSell->price) {
      throw new NotEnoughMoneyException('Not enough money inserted');
    }
    if ($this->moneyInserted > $itemToSell->price) {
      $change = $this->cashBox->getCoinsForChange($this->moneyInserted, $itemToSell->price);
    }
    $this->itemsInventory->removeOneItem($itemType);
    $this->moneyInserted = 0;
    $this->revenue += $itemToSell->price;
    return new Sale($change, $itemToSell);
  }

  public function cashBack(): array
  {
    $change = $this->cashBox->getValueInCoins($this->moneyInserted);
    $this->moneyInserted = 0;
    return $change;
  }

  public function getCashAvailable(): array
  {
    return $this->cashBox->getCashQuantities();
  }

  public function getRevenue(): int
  {
    return $this->revenue;
  }

  public function updateItemQuantity(SupportedItems $itemType, int $quantity): void
  {
    $this->itemsInventory->updateItemQuantity($itemType, $quantity);
  }

  public function setCashAvailable(CashBox $cashBox): void
  {
    $this->cashBox = $cashBox;
  }
}
