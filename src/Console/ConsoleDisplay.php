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
      implode(', ', array_map(fn($coin) => $coin->getValueInCents() / 100, $coins))
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

  public function showServiceModeEntered(): void
  {
    echo "\nEntering service mode...\n";
  }

  public function showServiceModeExited(): void
  {
    echo "\nExiting service mode...\n";
  }

  public function showServiceHelp(array $commands): void
  {
    echo "\nService Mode Commands:\n";
    foreach ($commands as $command => $description) {
      echo sprintf("  %-10s : %s\n", $command, $description);
    }
  }

  public function showMachineStatus(array $inventory): void
  {
    echo "\nMachine Status:\n";
    echo "Inventory:\n";
    foreach ($inventory as $item) {
      echo sprintf("  %s: %d\n", $item['item']->selector, $item['quantity']);
    }
  }

  public function showItems(array $items): void
  {
    echo "\nCurrent Items Inventory:\n";
    foreach ($items as $item => $quantity) {
      echo sprintf("  %-10s : %d\n", $item, $quantity);
    }
  }

  public function showCash(array $cash): void
  {
    echo "\nCurrent Cash Inventory:\n";
    foreach ($cash as $value => $quantity) {
      echo sprintf("  %-10s : %d coins\n", $value, $quantity);
    }
  }

  public function showRevenue(int $revenue): void
  {
    echo sprintf("\nTotal Revenue: %.2f €\n", $revenue / 100);
  }

  public function showMessage(string $message): void
  {
    echo $message;
  }

  public function showServiceItemName(string $prompt): void
  {
    echo $prompt;
  }

  public function showServiceItemQuantity(string $prompt): void
  {
    echo $prompt;
  }
}
