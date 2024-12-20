<?php

namespace App\Console;

use App\Actions;
use App\Domain\Coin\{CashBox, Coin, SupportedCoins};
use App\Domain\Item\ItemInventory;
use App\Domain\Item\{Item, SupportedItems};
use App\Domain\VendorMachine;

class Console
{
    private bool $running = true;
    private bool $serviceMode = false;
    private VendorMachine $vendorMachine;
    private ConsoleDisplay $display;

    public function __construct()
    {
        $cashBox = array_fill_keys(
            array_map(fn (SupportedCoins $coinType) => $coinType->value, SupportedCoins::cases()),
            5
        );
        $itemInventory = new ItemInventory();
        $itemInventory->setItem(new Item(SupportedItems::JUICE, 100), 10);
        $itemInventory->setItem(new Item(SupportedItems::SODA, 150), 10);
        $itemInventory->setItem(new Item(SupportedItems::WATER, 100), 10);
        $this->vendorMachine = new VendorMachine(new CashBox($cashBox), $itemInventory);
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
                SupportedItems::JUICE->value => $this->buyItem(SupportedItems::JUICE),
                SupportedItems::SODA->value => $this->buyItem(SupportedItems::SODA),
                SupportedItems::WATER->value => $this->buyItem(SupportedItems::WATER),
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
            'exit' => 'Exit service mode',
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
        $actionCommands = [];
        foreach (Actions::cases() as $action) {
            $actionCommands[$action->value] = $action->getDescription();
        }
        $itemCommands = [];
        foreach (SupportedItems::cases() as $item) {
            $itemCommands[$item->value] = "Buy a {$item->value}";
        }
        $commands = $actionCommands + $itemCommands;
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

    private function buyItem(SupportedItems $itemType): void
    {
        $sale = $this->vendorMachine->buy($itemType);
        $this->display->showPurchaseSuccess($sale->item->selector, $sale->change);
    }

    private function showItems(): void
    {
        $inventory = $this->vendorMachine->getInventory();
        $this->display->showItems($inventory);
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
        $this->display->showServiceItemName('Enter item name (' . implode('/', SupportedItems::getValues()) . '): ');
        $itemName = trim(fgets(STDIN));

        if (SupportedItems::tryFrom($itemName) === null) {
            throw new \InvalidArgumentException('Invalid item name');
        }

        try {
            $this->display->showServiceItemQuantity('Enter quantity: ');
            $quantity = (int)trim(fgets(STDIN));
            $this->vendorMachine->updateItemQuantity(SupportedItems::from($itemName), $quantity);
            $this->display->showMessage("Items inventory updated successfully\n");
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
            if ($quantity < 0) {
                throw new \InvalidArgumentException('Quantity cannot be negative');
            }
            $cash[$coinType->value] = $quantity;
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
