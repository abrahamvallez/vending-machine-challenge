<?php

namespace App\Console;

class ConsoleDisplay
{
  public function showWelcome(): void
  {
    echo "Vendor Machine\n";
    echo "Type 'help' to see available commands\n";
  }

  public function showPrompt(): void
  {
    echo "\n> ";
  }

  public function showHelp(array $commands): void
  {
    echo "\nAvailable commands:\n";
    foreach ($commands as $command => $description) {
      echo sprintf("  %-10s : %s\n", $command, $description);
    }
  }

  public function showChange(array $coins): void
  {
    echo sprintf(
      "Here's your change: %s\n",
      implode(', ', array_map(fn($coin) => $coin->value, $coins))
    );
  }

  public function showPurchaseSuccess(string $itemName, array $change = []): void
  {
    echo sprintf("Here's your %s, thank you\n", $itemName);
    if (count($change) > 0) {
      $this->showChange($change);
    }
  }

  public function showError(string $message): void
  {
    echo "Error: $message\n";
  }
}
