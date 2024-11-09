<?php

namespace App;

use App\Domain\VendorMachine;
use App\Domain\Coin;
use App\Domain\CoinInventory;
use App\Domain\SupportedCoins;
use App\Domain\SupportedItems;
use App\Domain\Item;

class Console
{
    private bool $running = true;
    private array $commands = [];
    private VendorMachine $vendorMachine;

    public function __construct()
    {
        $coinInventory = array_fill_keys(array_map(fn(SupportedCoins $coin) => $coin->value, SupportedCoins::cases()), 5);
        $this->vendorMachine = new VendorMachine(new CoinInventory($coinInventory));
        $this->commands = [
            'help' => 'Shows the list of available commands',
            '1' => 'Insert one euro',
            '0.25' => 'Insert 25 euro cents',
            '0.10' => 'Insert 10 euro cents',
            '0.05' => 'Insert 5 euro cents',
            'JUICE' => 'Buy a juice',
            'SODA' => 'Buy a soda',
            'WATER' => 'Buy a water',
            'cash-back' => 'Get money back',
            'exit' => 'Exit application',
            'clear' => 'Clear screen',
        ];
    }

    public function run(): void
    {
        echo "Vendor Machine\n";
        echo "Type 'help' to see available commands\n";

        while ($this->running) {
            echo "\n> ";
            $input = trim(fgets(STDIN));
            $this->processCommand($input);
        }
    }

    private function processCommand(string $input): void
    {
        switch ($input) {
            case 'help':
                $this->showHelp();
                break;

            case 'exit':
                $this->running = false;
                echo "See you later, alligator!\n";
                break;
            case 'cash-back':
                $change = $this->vendorMachine->cashBack();
                echo sprintf("Here's your change: %s\n", implode(', ', array_map(fn(Coin $coin) => $coin->value, $change)));
                break;
            case '1':
                $this->vendorMachine->insertCoin(Coin::oneEuro());
                break;
            case '0.25':
                $this->vendorMachine->insertCoin(Coin::quarter());
                break;
            case '0.10':
                $this->vendorMachine->insertCoin(Coin::ten());
                break;
            case '0.05':
                $this->vendorMachine->insertCoin(Coin::nickel());
                break;
            case SupportedItems::JUICE->name:
                $this->buyItem(SupportedItems::JUICE);
                break;
            case SupportedItems::SODA->name:
                $this->buyItem(SupportedItems::SODA);
                break;
            case SupportedItems::WATER->name:
                $this->buyItem(SupportedItems::WATER);
                break;
            default:
                echo "Command not supported. Type 'help' to see available commands.\n";
        }
    }

    protected function showHelp(): void
    {
        echo "\nAvailable commands:\n";
        foreach ($this->commands as $command => $description) {
            echo sprintf("  %-10s : %s\n", $command, $description);
        }
    }

    protected function buyItem(SupportedItems $item): void
    {
        try {
            $sale = $this->vendorMachine->buy(new Item($item->name, $item->value));
            echo sprintf("Here's your %s, thank you\n", $sale->item->name);
            if (count($sale->change) > 0) {
                echo sprintf("Here's your change: %s\n", implode(', ', array_map(fn(Coin $coin) => $coin->value, $sale->change)));
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
}
