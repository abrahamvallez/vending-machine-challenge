<?php

declare(strict_types=1);

namespace App\Domain\Coin;

use App\Domain\Exceptions\NotEnoughChangeException;

class CashBox
{
    private array $cashQuantities;

    public function __construct(array $cashQuantities = [])
    {
        $this->setCashQuantitiesTo0();
        $this->setCashQuantitiesFrom(
            $cashQuantities
        );
    }

    public function getCashQuantities(): array
    {
        return $this->cashQuantities;
    }

    public function addCoin(Coin $coin): void
    {
        $this->cashQuantities[$coin->getValueInCents()]++;
    }

    /**
     * Calculates and provides change for a purchase
     * 
     * @param int $moneyInserted Total money inserted in cents
     * @param int $itemPrice Price of the item in cents
     * @return Coin[] Array of coins representing the change
     * @throws NotEnoughChangeException When exact change cannot be provided
     */
    public function getCoinsForChange(int $moneyInserted, int $itemPrice): array
    {
        $remainingChange = $moneyInserted - $itemPrice;
        if ($remainingChange < 0) {
            return [];
        }
        return $this->calculateChange($remainingChange);
    }

    /**
     * Converts a value into an array of coins
     * 
     * @param int $value Amount to convert in cents
     * @return Coin[] Array of coins totaling the requested value
     * @throws NotEnoughChangeException When the value cannot be represented with available coins
     */
    public function getValueInCoins(int $value): array
    {
        return $this->calculateChange($value);
    }

    private function setCashQuantitiesTo0(): void
    {
        foreach (SupportedCoins::cases() as $coinType) {
            $this->cashQuantities[$coinType->value] = 0;
        }
    }

    private function setCashQuantitiesFrom(array $cashQuantities): void
    {
        foreach ($cashQuantities as $coinValue => $quantity) {
            $this->cashQuantities[$coinValue] = $quantity;
        }
    }

    private function calculateChange(int $value): array
    {
        $change = [];
        foreach (SupportedCoins::getValues() as $coinValue) {
            while ($value >= $coinValue && $this->cashQuantities[$coinValue] > 0) {
                $value -= $coinValue;
                $change[] = Coin::fromValueOnCents($coinValue);
                $this->cashQuantities[$coinValue]--;
            }
        }
        if ($value > 0) {
            throw new NotEnoughChangeException();
        }
        return $change;
    }
}
