<?php

declare(strict_types=1);

namespace App\Domain\Coin;

use App\Domain\Exceptions\NotEnoughChangeException;

class CoinInventory
{
    private array $quantities;

    /**
     * Initializes quantities for all supported coin types
     * 
     * @param array<int, int> $coinInventory Initial quantities for each coin type
     */
    public function __construct(array $coinInventory = [])
    {
        $this->setQuantitiesTo0();
        $this->setQuantitiesFrom($coinInventory);
    }

    /**
     * Gets current quantities of all coin types
     * 
     * @return array<int, int> Map of coin values to their quantities
     */
    public function getQuantities(): array
    {
        return $this->quantities;
    }

    public function addCoin(Coin $coin): void
    {
        $this->quantities[$coin->getValueInCents()]++;
    }

    public function getCoinsForChange(int $moneyInserted, int $itemPrice): array
    {
        $remainingChange = $moneyInserted - $itemPrice;
        if ($remainingChange < 0) {
            return [];
        }
        return $this->calculateChange($remainingChange);
    }

    public function getValueInCoins(int $value): array
    {
        return $this->calculateChange($value);
    }

    private function setQuantitiesTo0(): void
    {
        $this->quantities = array_fill_keys(
            array_map(
                fn($coinType) => $coinType->value,
                SupportedCoins::cases()
            ),
            0
        );
    }

    private function setQuantitiesFrom(array $coinInventory): void
    {
        foreach ($coinInventory as $coinValue => $quantity) {
            $coin = Coin::fromValueOnCents($coinValue);
            $this->quantities[$coinValue] = $quantity;
        }
    }

    private function calculateChange(int $value): array
    {
        $change = [];
        foreach (SupportedCoins::cases() as $coinType) {
            while ($value >= $coinType->value && $this->quantities[$coinType->value] > 0) {
                $value -= $coinType->value;
                $change[] = Coin::fromValueOnCents($coinType->value);
                $this->quantities[$coinType->value]--;
            }
        }
        if ($value > 0) {
            throw new NotEnoughChangeException();
        }
        return $change;
    }
}
