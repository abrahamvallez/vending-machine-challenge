<?php

namespace App;

enum Actions: string
{
    case HELP = 'help';
    case EXIT = 'exit';
    case CASH_BACK = 'cash-back';
    case SERVICE = 'service';
    case ONE_EURO = '1';
    case QUARTER = '0.25';
    case TEN_CENTS = '0.10';
    case NICKEL = '0.05';

    public function getDescription(): string
    {
        return match ($this) {
            self::HELP => 'Shows the list of available commands',
            self::EXIT => 'Exit application',
            self::CASH_BACK => 'Get money back',
            self::SERVICE => 'Enter service mode',
            self::ONE_EURO => 'Insert one euro',
            self::QUARTER => 'Insert 25 euro cents',
            self::TEN_CENTS => 'Insert 10 euro cents',
            self::NICKEL => 'Insert 5 euro cents',
        };
    }
}
