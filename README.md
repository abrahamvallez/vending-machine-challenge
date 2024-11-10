# PHP Vending Machine Console Application

A command-line vending machine simulator built with PHP. The application allows users to insert coins, buy items, and includes a service mode for maintenance operations.

## Features

- Insert different coin denominations (€1, €0.25, €0.10, €0.05)
- Buy items (juice, soda, water)
- Get change back
- Service mode for maintenance
- Inventory management
- Cash management

## Requirements

- PHP 8.2 or higher
- Composer
- Docker (optional)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
```

2. Install dependencies:
```bash
composer install
```

## Running the Application

### Using Docker

1. Build and start the container:
```bash
docker-compose up -d
```

2. Access the application:
```bash
docker-compose exec php php src/App.php
```

### Without Docker

```bash
php src/App.php
```

## Available Commands

### Regular Mode

| Command    | Description                |
|------------|----------------------------|
| help       | Shows available commands   |
| exit       | Exit application          |
| cash-back  | Get money back            |
| service    | Enter service mode        |
| 1          | Insert one euro           |
| 0.25       | Insert 25 euro cents      |
| 0.10       | Insert 10 euro cents      |
| 0.05       | Insert 5 euro cents       |
| juice      | Buy a juice (€1.00)       |
| soda       | Buy a soda (€1.50)        |
| water      | Buy a water (€0.65)       |

### Service Mode

| Command    | Description                |
|------------|----------------------------|
| help       | Show service mode commands |
| status     | Show machine status        |
| items      | Show items inventory       |
| cash       | Show cash inventory        |
| revenue    | Show total revenue         |
| set-item   | Set item quantity         |
| set-cash   | Set coin quantity         |
| exit       | Exit service mode         |

## Development

### Running Tests

```bash
composer test
```

### Code Style

Check code style:
```bash
composer check-style
```

Fix code style:
```bash
composer lint:fix
```

## Project Structure

- `src/` - Application source code
  - `Console/` - Console interface implementation
  - `Domain/` - Domain
    - `Coin/` - Coin-related Domain
    - `Item/` - Item-related Domain
    - `Exceptions/` - Domain exceptions
- `tests/` - Test files
