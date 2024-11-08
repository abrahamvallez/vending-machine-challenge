<?php

namespace App\Ui;

class Console
{
    private bool $running = true;
    private array $commands = [];

    public function __construct()
    {
        $this->commands = [
            'help' => 'Muestra la lista de comandos disponibles',
            'exit' => 'Termina la aplicación',
            'juice' => 'Muestra la hora actual',
            'clear' => 'Limpia la pantalla',
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

            case 'juice':
                system('clear');
                break;
            default:
                echo "Command not supported. Type 'help' to see available commands.\n";
        }
    }

    private function showHelp(): void
    {
        echo "\nAvailable commands:\n";
        foreach ($this->commands as $command => $description) {
            echo sprintf("  %-10s : %s\n", $command, $description);
        }
    }
}
