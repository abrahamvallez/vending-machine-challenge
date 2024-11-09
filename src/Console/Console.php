<?php

namespace App\Console;

use App\Actions;
use App\Console\ConsoleDisplay;
use App\Domain\VendorMachine;
use App\Domain\Coin;
use App\Domain\CoinInventory;
use App\SupportedCoins;
use App\SupportedItems;
use App\Domain\Item;

class Console
{
    private bool $running = true;
    private VendorMachine $vendorMachine;
    private ConsoleDisplay $display;

    public function __construct()
    {
        $coinInventory = array_fill_keys(
            array_map(fn(SupportedCoins $coin) => $coin->value, SupportedCoins::cases()),
            5
        );
        $this->vendorMachine = new VendorMachine(new CoinInventory($coinInventory));
        $this->display = new ConsoleDisplay();
    }

    public function run(): void
    {
        $this->display->showWelcome();

        while ($this->running) {
            $this->display->showPrompt();
            $input = trim(fgets(STDIN));
            $this->processCommand($input);
        }
    }

    private function processCommand(string $input): void
    {
        try {
            match ($input) {
                Actions::HELP->value => $this->showHelp(),
                Actions::EXIT->value => $this->exit(),
                Actions::CASH_BACK->value => $this->processCashBack(),
                Actions::ONE_EURO->value => $this->vendorMachine->insertCoin(Coin::oneEuro()),
                Actions::QUARTER->value => $this->vendorMachine->insertCoin(Coin::quarter()),
                Actions::TEN_CENTS->value => $this->vendorMachine->insertCoin(Coin::ten()),
                Actions::NICKEL->value => $this->vendorMachine->insertCoin(Coin::nickel()),
                SupportedItems::JUICE->name => $this->buyItem(SupportedItems::JUICE),
                SupportedItems::SODA->name => $this->buyItem(SupportedItems::SODA),
                SupportedItems::WATER->name => $this->buyItem(SupportedItems::WATER),
                default => $this->display->showError("Command not supported. Type 'help' to see available commands.")
            };
        } catch (\Throwable $th) {
            $this->display->showError($th->getMessage());
        }
    }

    private function showHelp(): void
    {
        $commands = array_merge(
            array_combine(
                array_column(Actions::cases(), 'value'),
                array_map(fn($command) => $command->getDescription(), Actions::cases())
            ),
            array_combine(
                array_column(SupportedItems::cases(), 'name'),
                array_map(fn($item) => "Buy a {$item->name}", SupportedItems::cases())
            )
        );

        $this->display->showHelp($commands);
    }

    private function exit(): void
    {
        $this->running = false;
        echo "See you later, alligator!\n";
    }

    private function processCashBack(): void
    {
        $change = $this->vendorMachine->cashBack();
        $this->display->showChange($change);
    }

    private function buyItem(SupportedItems $item): void
    {
        $sale = $this->vendorMachine->buy(new Item($item->name, $item->value));
        $this->display->showPurchaseSuccess($sale->item->name, $sale->change);
    }
}
