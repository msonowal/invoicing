# Laravel Invoicing App - CLAUDE.md

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
# Run all tests
sail test

# Run specific test
sail test --filter [TestName]

# Run tests with coverage
sail test --coverage
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

## Development Guidelines

### Commit Guidelines
- Use atomic, conventional commits
- All Pest tests must pass before commit
- Commit regularly with meaningful messages
- Use format: `feat:`, `fix:`, `refactor:`, `test:`, `docs:`

### Code Standards
- All monetary values stored as integers (never floats)
- Use Value Objects for complex data structures
- Implement custom casts for JSON columns
- Write comprehensive Pest tests
- Follow Laravel conventions

### Database Schema Notes
- Use polymorphic relationships for locations
- Store emails as JSON with Value Objects
- Use enums for type and status fields
- All monetary columns as integer type

## Project Structure

### Models
- `Company` - Business entity with locations and emails
- `Customer` - Client entity with locations and emails  
- `Location` - Polymorphic model for addresses
- `Invoice` - Unified model for invoices and estimates (with ULID)
- `InvoiceItem` - Line items for invoices

### Value Objects
- `EmailCollection` - Manages email arrays from JSON
- `InvoiceTotals` - Encapsulates invoice calculation results

### Custom Casts
- `EmailCollectionCast` - Converts JSON to EmailCollection

### Services
- `InvoiceCalculator` - Handles invoice calculations
- `EstimateToInvoiceConverter` - Converts estimates to invoices
- `DocumentMailer` - Handles document emailing
- `PdfService` - PDF generation using Spatie Browsershot

## Testing Strategy
- Unit tests for Value Objects and custom casts
- Feature tests for all CRUD operations
- Integration tests for services
- All tests must pass before commits

## URL Structure
- `/companies` - Company management
- `/customers` - Customer management
- `/invoices` - Invoice and estimate management
- `/invoices/{ulid}` - Public invoice view
- `/estimates/{ulid}` - Public estimate view
- `/invoices/{ulid}/pdf` - Download invoice PDF
- `/estimates/{ulid}/pdf` - Download estimate PDF

## Notes
- Application runs on http://localhost
- PDF generation implemented with Spatie Browsershot
- Uses ULID instead of UUID for better performance and sorting
- Single currency for initial phase
- Focus on core invoicing functionality with professional PDF output

## PDF Generation Setup
- Requires Node.js and Puppeteer in the container
- PDF templates optimized for print with proper styling
- A4 page size with professional formatting
- Includes company/customer details, line items, and totals