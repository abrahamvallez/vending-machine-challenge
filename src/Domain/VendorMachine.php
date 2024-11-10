<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Coin\CashBox;
use App\Domain\Coin\Coin;
use App\Domain\Exceptions\NotEnoughInventoryException;
use App\Domain\Exceptions\NotEnoughMoneyException;
use App\Domain\Item\ItemInventory;
use App\Domain\Item\SupportedItems;

class VendorMachine
{
  private int $moneyInserted = 0;
  private int $revenue = 0;

  public function __construct(
    private CashBox $cashBox = new CashBox(),
    private ItemInventory $itemsInventory = new ItemInventory()
  ) {}

  public function insertCoin(Coin $coin): void
  {
    $this->moneyInserted += $coin->getValueInCents();
    $this->cashBox->addCoin($coin);
  }

  public function getInventory(): ItemInventory
  {
    return $this->itemsInventory;
  }

  /**
   * Processes a purchase attempt for an item
   * 
   * @param SupportedItems $itemType The type of item to purchase
   * @return Sale Contains the purchased item and any change due
   * @throws NotEnoughInventoryException When the item is out of stock
   * @throws NotEnoughMoneyException When insufficient money was inserted
   * @throws NotEnoughChangeException When the machine cannot provide correct change
   */
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

  /**
   * Returns all inserted money as coins
   * 
   * @return Coin[] Array of coins representing the money to return
   * @throws NotEnoughChangeException When the machine cannot provide exact change
   */
  public function cashBack(): array
  {
    $change = $this->cashBox->getValueInCoins($this->moneyInserted);
    $this->moneyInserted = 0;
    return $change;
  }

  /**
   * Gets the current cash quantities available in the machine
   * 
   * @return array<int, int> Map of coin values to their quantities
   */
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
