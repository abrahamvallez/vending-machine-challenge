# PHP Vending Machine Console Application

A command-line vending machine simulator built with PHP. The application allows users to insert coins, buy items, and includes a service mode for maintenance operations.

### Core Functionality
1. **Money Handling**
   - Accepts multiple coin denominations (€1, €0.25, €0.10, €0.05)
   - Provides accurate change calculation
   - Handles insufficient change scenarios

2. **Inventory Management**
   - Real-time stock tracking
   - Product availability checks

3. **Service Mode**
   - Protected maintenance interface
   - Cash management
   - Inventory adjustment
   - Revenue tracking

## Available Commands

### Customer Mode
| Command    | Description                |
|------------|----------------------------|
| help       | Shows available commands   |
| 1          | Insert one euro           |
| 0.25       | Insert 25 euro cents      |
| 0.10       | Insert 10 euro cents      |
| 0.05       | Insert 5 euro cents       |
| cash-back  | Get money back            |
| juice      | Buy a juice (€1.00)       |
| soda       | Buy a soda (€1.50)        |
| water      | Buy a water (€0.65)       |

### Service Mode
| Command    | Description                |
|------------|----------------------------|
| status     | Show machine status        |
| items      | Show items inventory       |
| cash       | Show cash inventory        |
| revenue    | Show total revenue         |
| set-item   | Set item quantity         |
| set-cash   | Set coin quantity         |

## Requirements

- PHP 8.2 or higher
- Composer
- Docker (optional)

## Product Management

The development of this project was tracked using a GitHub Project board. You can view the detailed development progress and feature implementation at [GitHub Project Board](https://github.com/users/abrahamvallez/projects/3).

## Technical Stack
- PHP 8.2
- PHPUnit 11.4 for testing
- PHP_CodeSniffer & PHP-CS-Fixer for code quality
- Docker for containerization
- Composer for dependency management

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

## Architecture Decision Records (ADRs)

### ADR 1: UI Layer Implementation
**Context:**
The Console class serves as both UI layer and partially as application layer.

**Decision:**
We decided to combine UI and application layer logic in the Console class rather than creating a separate application layer.

**Consequences:**
* Positive:
  - Reduced complexity by avoiding unnecessary indirection
  - Simpler codebase for a console application
  - Direct mapping between user commands and domain actions
* Negative:
  - Less separation of concerns
  - Potential difficulty if we need to add new UI interfaces

### ADR 2: Domain Object Separation
**Context:**
ItemInventory and CashBox functionality was initially part of VendorMachine class.

**Decision:**
Extracted ItemInventory and CashBox as separate domain objects to handle their specific responsibilities.

**Consequences:**
* Positive:
  - Better adherence to Single Responsibility Principle
  - Reduced complexity in VendorMachine class
  - Easier to test and maintain each component
* Negative:
  - More classes to manage

### ADR 3: Testing Strategy
**Context:**
Need to establish a comprehensive testing approach for the domain model.

**Decision:**
Implemented a two-level testing strategy (see [Martin Fowler's article on Unit Testing](https://martinfowler.com/bliki/UnitTest.html)): 
1. Sociable tests for VendorMachine aggregate ()
2. Solitary unit tests for individual domain objects

**Consequences:**
* Positive:
  - High test coverage of business logic
  - Clear test organization
  - Easy to identify failing behaviors
* Negative:
  - UI layer currently untested (Next iterations)
  - Some test duplication between levels
  - Longer test execution time

### ADR 4: Domain Model Configuration
**Context:**
- Need to manage supported coins, items, and actions in a maintainable way
- Prior to PHP 8.1, similar functionality was typically achieved using class constants or static arrays
- PHP 8.1 introduced native enum support, providing a more robust way to handle fixed sets of values

**Decision:**
Used PHP 8.1 Enums to define supported domain values (coins, items, actions) instead of traditional approaches like:

**Consequences:**
* Positive:
  - Type-safe domain values
  - Native PHP support for enumerated types
  - Built-in methods like `cases()` and `from()`
  - IDE autocompletion support
  - Prevents invalid states and magic values
  - Self-documenting code
* Negative:
  - Higher PHP version requirement (8.1+)
  - Limited to simple value types
  - Not suitable for dynamic configurations

**Alternatives Considered:**
1. Class Constants:
   - More compatible but less type-safe
   - No built-in validation
   - No method support

2. Configuration Files:
   - More flexible for runtime changes
   - Less type safety
   - Too much complexity for this challenge

### ADR 5: Code Documentation Strategy
**Context:**
- Need to establish a consistent approach to code documentation
- Some methods are self-documenting through type hints and naming
- Other methods require additional context or explanation
- PHP 8.2 provides robust type system

**Decision:**
Implemented a selective PHPDoc documentation strategy:
1. Document only methods that:
   - Have complex return types (arrays with specific structures)
   - Can throw exceptions
   - Have non-obvious side effects
   - Require additional context
2. Rely on PHP's type system and descriptive naming for self-documenting code

**Consequences:**
* Positive:
  - Reduced documentation maintenance overhead
  - Clear focus on what needs explanation
  - Clean, readable codebase
  - Better IDE support for complex types
* Negative:
  - Requires good judgment about what needs documentation
  - May need to add documentation later as code evolves

These ADRs document key architectural decisions made during development, their context, and consequences.
