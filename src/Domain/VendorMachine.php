<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Exceptions\NotEnoughInventoryException;
use App\Domain\Exceptions\NotEnoughMoneyException;
use InvalidArgumentException;
use PHPUnit\Framework\TestStatus\Success;

class VendorMachine
{
  private int $moneyInserted = 0;

  public function __construct(private CoinInventory $coinInventory = new CoinInventory(), private array $itemsInventory = [
    SupportedItems::JUICE->name => 5,
    SupportedItems::SODA->name => 5,
    SupportedItems::WATER->name => 5,
  ]) {}

  public function insertCoin(Coin $coin): void
  {
    $this->moneyInserted += $coin->value;
    $this->coinInventory->addCoin($coin);
  }

  public function getInventory(): array
  {
    return $this->itemsInventory;
  }

  public function buy(Item $item): Sale
  {
    if (!SupportedItems::isCorrectItemName($item->name)) {
      throw new InvalidArgumentException('Item not supported: ' . $item->name);
    }

    $change = [];
    if ($this->itemsInventory[$item->name] === 0) {
      throw new NotEnoughInventoryException('Not enough inventory');
    }
    if ($this->moneyInserted < $item->value) {
      throw new NotEnoughMoneyException('Not enough money inserted');
    }
    if ($this->moneyInserted > $item->value) {
      $change = $this->coinInventory->getCoinsForChange($this->moneyInserted, $item->value);
    }
    $this->itemsInventory[$item->name]--;
    $this->moneyInserted = 0;
    return new Sale($change, $item);
  }
}
