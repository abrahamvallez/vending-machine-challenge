<?php

namespace App\Console;

use App\Actions;
use App\Console\ConsoleDisplay;
use App\Domain\VendorMachine;
use App\Domain\Coin\{CashBox, Coin, SupportedCoins};
use App\Domain\Item\{Item, SupportedItems};

class Console
{
    private bool $running = true;
    private bool $serviceMode = false;
    private VendorMachine $vendorMachine;
    private ConsoleDisplay $display;

    public function __construct()
    {
        $cashBox = array_fill_keys(
            array_map(fn(SupportedCoins $coinType) => $coinType->value, SupportedCoins::cases()),
            5
        );
        $this->vendorMachine = new VendorMachine(new CashBox($cashBox));
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
            if ($this->serviceMode) {
                $this->processServiceCommand($input);
                return;
            }

            match ($input) {
                Actions::HELP->value => $this->showHelp(),
                Actions::EXIT->value => $this->exit(),
                Actions::SERVICE->value => $this->enterServiceMode(),
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

    private function enterServiceMode(): void
    {
        $this->serviceMode = true;
        $this->display->showServiceModeEntered();
        $this->showServiceHelp();
    }

    private function processServiceCommand(string $input): void
    {
        match ($input) {
            'exit' => $this->exitServiceMode(),
            'help' => $this->showServiceHelp(),
            'items' => $this->showItems(),
            'cash' => $this->showCash(),
            'revenue' => $this->showRevenue(),
            'set-item' => $this->setItem(),
            'set-cash' => $this->setCash(),
            'status' => $this->showMachineStatus(),
            default => $this->display->showError("Invalid service command. Type 'help' for available commands.")
        };
    }

    private function exitServiceMode(): void
    {
        $this->serviceMode = false;
        $this->display->showServiceModeExited();
    }

    private function showServiceHelp(): void
    {
        $commands = [
            'help' => 'Show service mode commands',
            'status' => 'Show machine status',
            'items' => 'Show items inventory',
            'cash' => 'Show cash inventory',
            'revenue' => 'Show total revenue',
            'set-item' => 'Set item quantity',
            'set-cash' => 'Set coin quantity',
            'exit' => 'Exit service mode'
        ];
        $this->display->showServiceHelp($commands);
    }

    private function showMachineStatus(): void
    {
        $inventory = $this->vendorMachine->getInventory();
        $this->display->showMachineStatus($inventory);
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
        $sale = $this->vendorMachine->buy(new Item($item->selector, $item->price));
        $this->display->showPurchaseSuccess($sale->item->selector, $sale->change);
    }

    private function showItems(): void
    {
        $items = $this->vendorMachine->getInventory();
        $this->display->showItems($items);
    }

    private function showCash(): void
    {
        $cash = $this->vendorMachine->getCashAvailable();
        $this->display->showCash($cash);
    }

    private function showRevenue(): void
    {
        $revenue = $this->vendorMachine->getRevenue();
        $this->display->showRevenue($revenue);
    }

    private function setItem(): void
    {
        $this->display->showServiceItemName("Enter item name (WATER/SODA/JUICE/...): ");
        $itemName = trim(fgets(STDIN));

        if (!Item::isSupportedItem($itemName)) {
            throw new \InvalidArgumentException('Invalid item name');
        }

        $this->display->showServiceItemQuantity("Enter quantity: ");
        $quantity = (int)trim(fgets(STDIN));

        try {
            $this->vendorMachine->setItemQuantity($itemName, $quantity);
            $this->display->showMessage("Item quantity updated successfully\n");
        } catch (\Throwable $th) {
            $this->display->showError($th->getMessage());
        }
    }

    private function setCash(): void
    {
        $cash = [];
        foreach (SupportedCoins::cases() as $coinType) {
            $this->display->showMessage("Enter quantity for {$coinType->value} cents: ");
            $quantity = (int)trim(fgets(STDIN));
            if ($quantity > 0) {
                $cash[$coinType->value] = $quantity;
            }
        }

        try {
            $cashBox = new CashBox($cash);
            $this->vendorMachine->setCashAvailable($cashBox);
            $this->display->showMessage("Cash amount updated successfully\n");
        } catch (\Throwable $th) {
            $this->display->showError($th->getMessage());
        }
    }
}
