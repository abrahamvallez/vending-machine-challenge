<?php

declare(strict_types=1);

namespace App\Domain\Coin;

class Coin
{
    private function __construct(
        private SupportedCoins $value
    ) {
    }

    public static function oneEuro(): self
    {
        return new self(SupportedCoins::ONE_EURO);
    }

    public static function quarter(): self
    {
        return new self(SupportedCoins::QUARTER);
    }

    public static function ten(): self
    {
        return new self(SupportedCoins::TEN);
    }

    public static function nickel(): self
    {
        return new self(SupportedCoins::NICKEL);
    }

    public static function fromValueOnCents(int $value): self
    {
        return new self(SupportedCoins::from($value));
    }

    public function getValueInCents(): int
    {
        return $this->value->value;
    }

    public static function coinsValue(array $coins): int
    {
        return array_sum(array_map(fn (Coin $coin) => $coin->getValueInCents(), $coins));
    }
}
