# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# Laravel Invoicing Application

## Project Configuration

### Environment
- **Laravel Version**: 12.19.3
- **PHP Version**: 8.4.8
- **Database**: PostgreSQL
- **UI Framework**: Livewire 3.6.3 + luvi-ui/laravel-luvi (shadcn for Livewire)
- **Testing**: Pest
- **Package Manager**: Yarn 4 (via corepack)
- **Container**: Laravel Sail

### Key Packages
- `akaunting/laravel-money` - For monetary value handling
- `luvi-ui/laravel-luvi` - shadcn UI components for Livewire
- `spatie/browsershot` - PDF generation using headless Chrome

## Development Commands

### Container Management
```bash
# Start all services
sail up -d

# Stop all services
sail down

# View logs
sail logs
```

### Laravel Commands
```bash
# Run migrations
sail php artisan migrate

# Create migration
sail php artisan make:migration [migration_name]

# Create model with migration
sail php artisan make:model [ModelName] -m

# Create Livewire component
sail php artisan make:livewire [ComponentName]

# Create custom cast
sail php artisan make:cast [CastName]

# Create mailable
sail php artisan make:mail [MailableName]

# Clear caches
sail php artisan config:clear
sail php artisan cache:clear
sail php artisan view:clear
```

### Testing Commands
```bash
# Fresh database migration before tests (ALWAYS run this first)
sail php artisan migrate:fresh --env=testing

# Run all tests
sail php artisan test

# Run specific test file
sail php artisan test tests/Unit/Models/InvoiceTest.php

# Run specific test by name filter
sail php artisan test --filter="can create invoice"

# Run tests with coverage
sail php artisan test --coverage

# Single test suite
sail php artisan test tests/Unit/
sail php artisan test tests/Feature/
```

### Database Commands
```bash
# Check migration status
sail php artisan migrate:status

# Fresh migration with seeding
sail php artisan migrate:fresh --seed

# Rollback migration
sail php artisan migrate:rollback
```

### Code Formatting Commands
```bash
# Format current uncommitted changes with Laravel Pint (ALWAYS run before commits)
sail pint --dirty

# Format specific files
sail pint app/Models/Invoice.php

# Check formatting without fixing
sail pint --test
```

### Frontend Commands
```bash
# Install dependencies (yarn berry)
sail yarn install

# Development build
sail yarn dev

# Production build  
sail yarn build
```

### Shell Access
```bash
# Access container shell for Linux commands
sail shell

# Access PostgreSQL directly
sail psql

# Access database via pgweb interface
# Open http://localhost:8081 in browser
```

## Architecture Overview

### Core Architectural Patterns

**Domain-Driven Design Influence:**
- Value Objects (`EmailCollection`, `InvoiceTotals`) encapsulate business logic
- Service Layer (`InvoiceCalculator`, `PdfService`, `EstimateToInvoiceConverter`) handles business operations
- Rich domain models with business methods (`Invoice::isInvoice()`, `InvoiceItem::getLineTotal()`)

**Data Model Architecture:**
- Polymorphic `Location` model serves both companies and customers
- ULID identifiers for public document sharing (better performance than UUID)
- Integer-based monetary storage (cents) to avoid floating-point precision issues
- Type-safe enums for status and type fields

**Key Relationships:**
```
Company -> Location (polymorphic, primary location)
Customer -> Location (polymorphic, primary location)  
Invoice -> Location (company & customer locations)
Invoice -> InvoiceItem (one-to-many)
```

### Development Guidelines

**Testing Requirements:**
- ALWAYS run `sail php artisan migrate:fresh --env=testing` before running tests
- All Pest tests must pass before commits
- Current coverage: 94.7% (maintain above 90%)
- Use `createInvoiceWithItems()` and other test helpers in `tests/TestHelpers.php`

**Code Standards:**
- All monetary values stored as integers (never floats)
- Use Value Objects for complex data structures
- Implement custom casts for JSON columns (`EmailCollectionCast`)
- Follow latest Laravel conventions (use `casts()` method, not `$casts` property)
- Avoid associative arrays - use proper object instances for data passing

**Money Handling:**
- Store all amounts in cents (integer) 
- Use akaunting/laravel-money package for formatting
- Default currency: INR (Indian Rupees)

**Commit Guidelines:**
- **ALWAYS run `sail pint --dirty` before every commit** to format uncommitted changes
- Atomic, conventional commits with format: `feat:`, `fix:`, `refactor:`, `test:`, `docs:`
- All tests must pass before commit
- Commit regularly with meaningful messages

### Key Components

**Models:**
- `Company` / `Customer` - Entities with polymorphic locations and EmailCollection emails
- `Location` - Polymorphic model serving both companies and customers
- `Invoice` - Unified model for both invoices and estimates (differentiated by `type` field)
- `InvoiceItem` - Line items with quantity, unit_price, tax_rate calculations

**Value Objects:**
- `EmailCollection` - Immutable collection with validation for multiple emails
- `InvoiceTotals` - Readonly class for subtotal, tax, total calculations

**Services:**
- `InvoiceCalculator` - Business logic for financial calculations
- `PdfService` - PDF generation using Spatie Browsershot (requires Puppeteer globally)
- `EstimateToInvoiceConverter` - Business logic for estimate-to-invoice conversion
- `DocumentMailer` - Email functionality for sending documents

**Livewire Components:**
- `CompanyManager` / `CustomerManager` - Full CRUD with location and email management
- `InvoiceWizard` - Multi-step wizard for creating invoices/estimates with real-time calculations

**Custom Casts:**
- `EmailCollectionCast` - Seamless JSON ↔ EmailCollection conversion with error handling

## URL Structure & Routes
- `/companies` - Company management (Livewire component)
- `/customers` - Customer management (Livewire component)  
- `/invoices` - Invoice and estimate management (Livewire component)
- `/invoices/{ulid}` - Public invoice view (no auth required)
- `/estimates/{ulid}` - Public estimate view (no auth required)
- `/invoices/{ulid}/pdf` - Download invoice PDF
- `/estimates/{ulid}/pdf` - Download estimate PDF

## Important Implementation Details

**PDF Generation:**
- Uses Spatie Browsershot with headless Chrome
- Puppeteer available globally via `npx` (not in package.json)
- A4 page format with professional styling
- Graceful error handling for container architecture issues

**Database Insights:**
- PostgreSQL with proper foreign key constraints
- Uses `RefreshDatabase` trait in ALL tests for isolation
- ULID primary keys for public document sharing
- Decimal(5,2) for tax_rate to support fractional rates (e.g., 12.5%)

**Livewire Architecture:**
- Full-stack components handle complete CRUD operations
- `#[Computed]` properties for efficient data loading
- Multi-step wizard pattern in InvoiceWizard
- Real-time calculation updates in UI

**Testing Infrastructure:**
- Pest framework with custom test helpers
- 222 tests with 94.7% coverage 
- Helper functions: `createCompanyWithLocation()`, `createInvoiceWithItems()`
- Edge case testing for large numbers, null values, decimal precision

**Package Management:**
- Yarn Berry (4.9.2) for frontend dependencies
- No package-lock.json (deleted - use yarn.lock only)
- Puppeteer available globally, not as project dependency

## Development Database
- pgweb interface available at http://localhost:8081
- Direct PostgreSQL access via `sail psql`
- All services accessible at http://localhost

## Git Workflow
- Always run `sail pint --dirty` to run pint formatter on current changes that are not commited before commit