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

  public function __construct(private CashBox $cashBox = new CashBox(), private array $itemsInventory = [
    SupportedItems::JUICE->value => ['item' => new Item(SupportedItems::JUICE, 100), 'quantity' => 5],
    SupportedItems::SODA->value => ['item' => new Item(SupportedItems::SODA, 150), 'quantity' => 5],
    SupportedItems::WATER->value => ['item' => new Item(SupportedItems::WATER, 65), 'quantity' => 5],
  ]) {}

  public function insertCoin(Coin $coin): void
  {
    $this->moneyInserted += $coin->getValueInCents();
    $this->cashBox->addCoin($coin);
  }

  public function getInventory(): array
  {
    return $this->itemsInventory;
  }

  public function buy(SupportedItems $itemType): Sale
  {
    $change = [];
    $itemSelector = $itemType->value;
    $itemToSell = $this->itemsInventory[$itemSelector]['item'];
    if ($this->itemsInventory[$itemSelector]['quantity'] === 0) {
      throw new NotEnoughInventoryException('Not enough inventory');
    }

    if ($this->moneyInserted < $itemToSell->price) {
      throw new NotEnoughMoneyException('Not enough money inserted');
    }
    if ($this->moneyInserted > $itemToSell->price) {
      $change = $this->cashBox->getCoinsForChange($this->moneyInserted, $itemToSell->price);
    }
    $this->itemsInventory[$itemSelector]['quantity']--;
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

  public function setItemQuantity(SupportedItems $itemType, int $quantity): void
  {
    $itemSelector = $itemType->value;
    if ($quantity < 0) {
      throw new InvalidArgumentException('Quantity cannot be negative');
    }
    $this->itemsInventory[$itemSelector]['quantity'] = $quantity;
  }

  public function setCashAvailable(CashBox $cashBox): void
  {
    $this->cashBox = $cashBox;
  }
}
