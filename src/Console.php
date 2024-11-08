<?php

namespace App;

use App\Domain\VendorMachine;

class Console
{
    private bool $running = true;
    private array $commands = [];
    private VendorMachine $vendorMachine;

    public function __construct()
    {
        $this->vendorMachine = new VendorMachine();
        $this->commands = [
            'help' => 'Shows the list of available commands',
            '1' => 'Insert one euro',
            '0.25' => 'Insert 25 euro cents',
            'exit' => 'Exit application',
            'juice' => 'Buy a juice',
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
                echo "¡Bye!\n";
                break;

            case '1':
                $this->vendorMachine->insertCoin(1);
                break;
            case '0.25':
                $this->vendorMachine->insertCoin(0.25);
                break;

            case 'juice':
                $this->buyJuice();
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

    protected function buyJuice(): void
    {
        try {
            $this->vendorMachine->buy('juice');
            echo "Here's your juice, thank you";
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
}
