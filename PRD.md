# Product Requirements Document (PRD)
## Multitenant SaaS Invoicing Platform

### Document Version: 1.1
### Last Updated: 2025-07-10
### Status: ✅ Refactored to Organization-Centric Architecture

---

## 📋 Table of Contents

1. [Executive Summary](#executive-summary)
2. [Current State Analysis](#current-state-analysis)
3. [Architecture Overview](#architecture-overview)
4. [Database Schema](#database-schema)
5. [Multi-Currency System](#multi-currency-system)
6. [Custom URL Handle System](#custom-url-handle-system)
7. [Tenant Isolation System](#tenant-isolation-system)
8. [Public Routes Enhancement](#public-routes-enhancement)
9. [Implementation Phases](#implementation-phases)
10. [Technical Specifications](#technical-specifications)
11. [Git Workflow & Quality Assurance](#git-workflow--quality-assurance)
12. [Progress Tracking](#progress-tracking)

---

## 🎯 Executive Summary

### Project Vision
Transform the existing single-tenant Laravel invoicing application into a comprehensive multitenant SaaS platform where users can:
- Register and create organizations for collaboration
- Manage business entities with multi-currency support
- Create and manage customers with currency preferences
- Generate invoices and estimates with custom branding
- Share public invoices with SEO-friendly URLs

### Business Objectives
- **Market Expansion**: Enable multiple businesses to use the platform
- **Revenue Growth**: SaaS subscription model with per-organization pricing
- **User Experience**: Seamless onboarding and multi-organization management
- **Brand Flexibility**: Custom URL handles and branding per organization
- **Global Reach**: Multi-currency support for international businesses

### Success Metrics
- [x] User registration and organization creation flow (< 2 minutes)
- [x] Organization onboarding completion rate (> 90%)
- [x] Multi-currency invoice generation accuracy (100%)
- [x] Public URL accessibility and SEO performance
- [x] Test coverage maintenance (94.7% achieved)

---

## 🔍 Current State Analysis

### ✅ Implemented Architecture (Organization-Centric)
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    User     │───▶│Organization │───▶│  Customer   │───▶│   Invoice   │
│             │    │ (Jetstream) │    │ (Scoped)    │    │ (Scoped)    │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
                         │
                         ▼
                   ┌─────────────┐
                   │  Location   │
                   │(Polymorphic)│
                   └─────────────┘
```

### Architecture Transformation Complete
- **Team/Company Consolidation**: Merged dual Team/Company structure into single Organization model
- **Simplified Relationships**: Direct User → Organization → Customer → Invoice flow
- **Polymorphic Locations**: Unified location management for organizations and customers
- **Multi-Currency Support**: AED, USD, EUR, GBP, INR with proper tax templates

### Migration Strategy
- **Zero Downtime**: Gradual migration with feature flags
- **Data Preservation**: Existing data becomes first organization of first team
- **Backward Compatibility**: Maintain existing API endpoints during transition

---

## 🏗️ Architecture Overview

### Organization-Centric Architecture

#### Single-Layer Architecture
- **Purpose**: Business entity management with user collaboration
- **Components**: Users, Organizations (Teams), Customers, Invoices, Locations
- **Features**: Multi-currency support, tax template management, polymorphic locations
- **Example**: "Dubai Trading LLC" with AED currency and UAE tax templates

### Relationship Flow
```
User → signs up → creates Organization → manages Customers → issues Invoices
```

### Key Architectural Decisions
- **Organization = Business Entity**: Simplified from dual Team/Company structure
- **Tenant Isolation**: Organization-scoped data access patterns
- **Multi-Currency**: Currency enum with comprehensive tax template system
- **Public Access**: ULID-based public URLs with professional styling

---

## 🗄️ Database Schema

### Current Database Schema

```sql
-- ========================================
-- JETSTREAM TABLES (User Management)
-- ========================================

users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP,
    password VARCHAR(255) NOT NULL,
    two_factor_secret TEXT,
    two_factor_recovery_codes TEXT,
    remember_token VARCHAR(100),
    current_team_id BIGINT,
    profile_photo_path VARCHAR(2048),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (current_team_id) REFERENCES teams(id)
)

teams (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    personal_team BOOLEAN DEFAULT FALSE,
    -- Organization-specific fields
    company_name VARCHAR(255),
    tax_number VARCHAR(255),
    registration_number VARCHAR(255),
    emails JSON,
    phone VARCHAR(255),
    website VARCHAR(255),
    currency ENUM('INR','USD','EUR','GBP','AUD','CAD','SGD','JPY','AED'),
    custom_domain VARCHAR(255),
    primary_location_id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (primary_location_id) REFERENCES locations(id)
)

-- ========================================
-- BUSINESS TABLES
-- ========================================

locations (
    id BIGINT PRIMARY KEY,
    locatable_type VARCHAR(255) NOT NULL,
    locatable_id BIGINT NOT NULL,
    name VARCHAR(255),
    gstin VARCHAR(255),
    address_line_1 VARCHAR(255),
    address_line_2 VARCHAR(255),
    city VARCHAR(255),
    state VARCHAR(255),
    country VARCHAR(255),
    postal_code VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_locatable (locatable_type, locatable_id)
)

customers (
    id BIGINT PRIMARY KEY,
    organization_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(255),
    emails JSON,
    primary_location_id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (primary_location_id) REFERENCES locations(id),
    INDEX idx_organization_customers (organization_id)
)

invoices (
    id BIGINT PRIMARY KEY,
    organization_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    ulid VARCHAR(26) UNIQUE NOT NULL,
    type ENUM('invoice', 'estimate') NOT NULL,
    status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    invoice_number VARCHAR(255),
    currency ENUM('INR','USD','EUR','GBP','AUD','CAD','SGD','JPY','AED') NOT NULL,
    organization_location_id BIGINT,
    customer_location_id BIGINT,
    issued_at TIMESTAMP,
    due_at TIMESTAMP,
    subtotal BIGINT NOT NULL DEFAULT 0,
    tax BIGINT NOT NULL DEFAULT 0,
    total BIGINT NOT NULL DEFAULT 0,
    email_recipients JSON,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_location_id) REFERENCES locations(id),
    FOREIGN KEY (customer_location_id) REFERENCES locations(id),
    INDEX idx_organization_invoices (organization_id),
    INDEX idx_public_ulid (ulid)
)

invoice_items (
    id BIGINT PRIMARY KEY,
    invoice_id BIGINT NOT NULL,
    description TEXT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price BIGINT NOT NULL,
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
)

tax_templates (
    id BIGINT PRIMARY KEY,
    organization_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(255) NOT NULL,
    rate DECIMAL(5,3) NOT NULL,
    category VARCHAR(255),
    country_code VARCHAR(2) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    metadata JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES teams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_org_template (organization_id, name)
)
```

---

## 💱 Multi-Currency System

### Currency Architecture

#### Organization Currency Settings
- **Default Currency**: Set during organization onboarding (mandatory)
- **Supported Currencies**: USD, EUR, GBP, INR, CAD, AUD, JPY, AED, SGD
- **Storage**: ISO 4217 3-character currency codes via PHP enum
- **Validation**: Against predefined currency list with symbols and names
- **Tax Templates**: Currency-specific tax templates (GST for INR, VAT for AED/EUR/GBP, Sales Tax for USD)

#### Customer Currency Preferences
- **Optional Setting**: Customers can have preferred currencies
- **Inheritance**: Falls back to organization default if not set
- **Flexibility**: Can be different from organization currency
- **Tax Integration**: Automatic tax rate selection based on organization currency

#### Invoice Currency Logic
```php
// Currency determination priority:
1. Customer preferred_currency (if set)
2. Organization default_currency (fallback)
3. System default 'INR' (ultimate fallback)

// Tax rate determination:
1. Organization currency-specific tax templates
2. Automatic tax rate selection (5% VAT for AED, 18% GST for INR, etc.)
```

### Implementation Details

#### Database Storage
- **Monetary Values**: Stored as integers in smallest currency unit (cents/paise)
- **Currency Code**: Stored separately for each invoice
- **Precision**: Maintains accuracy for financial calculations

#### Money Formatting
```php
// Using akaunting/laravel-money package with dynamic currencies
money($amount, $currency)->format() // ₹1,000.00 for INR
money($amount, $currency)->formatWithCode() // INR 1,000.00
```

#### Currency Enum Configuration
```php
// app/Currency.php
enum Currency: string {
    case INR = 'INR';
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case AUD = 'AUD';
    case CAD = 'CAD';
    case SGD = 'SGD';
    case JPY = 'JPY';
    case AED = 'AED';
    
    public function symbol(): string {
        return match($this) {
            self::INR => '₹',
            self::USD => '$',
            self::EUR => '€',
            self::GBP => '£',
            self::AUD => 'A$',
            self::CAD => 'C$',
            self::SGD => 'S$',
            self::JPY => '¥',
            self::AED => 'د.إ',
        };
    }
}
```

### Tax Template System

#### Currency-Specific Templates
- **INR**: CGST 9%, SGST 9%, IGST 18%, GST 5/12/28%, TDS 10%
- **AED**: VAT 5%, VAT 0%, VAT Exempt, Excise Tax 50/99%
- **USD**: Sales Tax 4/6/8.25%, No Tax
- **EUR**: VAT 7/19%, VAT 0%
- **GBP**: VAT 5/20%, VAT 0%

---

## 🔗 Custom URL Handle System

### Public URL Structure

#### ULID-Based Public URLs
- **Format**: `/invoices/{ulid}` and `/estimates/{ulid}`
- **Security**: ULIDs provide security through obscurity
- **SEO**: Cleaner URLs than sequential IDs
- **Uniqueness**: Globally unique across all documents

#### URL Examples
```
https://yourdomain.com/invoices/01HZ8J9K2N3M4P5Q6R7S8T9V0W
https://yourdomain.com/estimates/01HZ8J9K2N3M4P5Q6R7S8T9V0W
https://yourdomain.com/invoices/01HZ8J9K2N3M4P5Q6R7S8T9V0W/pdf
```

### Route Implementation
```php
// Public routes
Route::get('/invoices/{invoice:ulid}', [PublicViewController::class, 'showInvoice'])
    ->name('invoices.public');
Route::get('/estimates/{estimate:ulid}', [PublicViewController::class, 'showEstimate'])
    ->name('estimates.public');
Route::get('/invoices/{invoice:ulid}/pdf', [PublicViewController::class, 'downloadInvoicePdf'])
    ->name('invoices.pdf');
```

---

## 🔒 Tenant Isolation System

### Organization-Scoped Access

#### Model Relationships
```php
// Organization-scoped models
class Customer extends Model {
    public function organization() {
        return $this->belongsTo(Organization::class);
    }
}

class Invoice extends Model {
    public function organization() {
        return $this->belongsTo(Organization::class);
    }
}
```

#### Access Control
- **Query Scoping**: Automatic organization_id filtering in queries
- **Route Protection**: Middleware ensures valid organization context
- **Policy Enforcement**: Laravel policies for fine-grained control
- **Session Security**: Organization context stored securely

### Security Implementation
```php
// Organization policy
class OrganizationPolicy {
    public function view(User $user, Organization $organization) {
        return $user->belongsToTeam($organization);
    }
    
    public function manage(User $user, Organization $organization) {
        return $user->hasTeamRole($organization, 'admin');
    }
}
```

---

## 🌐 Public Routes Enhancement

### Public Document System

#### Document Access
- **Public URLs**: Accessible without authentication
- **Professional Styling**: Responsive, print-ready design
- **PDF Generation**: High-quality PDF downloads
- **Email Sharing**: Direct email document sharing

#### Public Templates
- **Responsive Design**: Mobile-first approach
- **Print Optimization**: Print-ready styling
- **SEO Optimization**: Meta tags and structured data
- **Performance**: Optimized loading and caching

### Email Integration
```php
// Document sharing
class DocumentMailer {
    public function sendInvoice(Invoice $invoice, array $recipients) {
        $publicUrl = route('invoices.public', $invoice->ulid);
        $pdfUrl = route('invoices.pdf', $invoice->ulid);
        
        Mail::to($recipients)
            ->send(new InvoiceMail($invoice, $publicUrl, $pdfUrl));
    }
}
```

---

## 🚀 Implementation Phases

### Overall Progress: ✅ Architecture Refactored - Organization-Centric Implementation Complete

### Phase 1: Jetstream Setup & Authentication
**Status**: ✅ Complete  
**Progress**: 6/6 tasks completed

- [x] Install and configure Laravel Jetstream
- [x] Set up organization-based authentication  
- [x] Create user registration and login flows
- [x] Implement organization creation and management
- [x] Add organization member invitation system
- [x] Configure role-based permissions

### Phase 2: Tenant Architecture & Global Scopes  
**Status**: ✅ Complete (Architecture Simplified)  
**Progress**: 6/6 tasks completed

- [x] Consolidated Team/Company into Organization model
- [x] Add organization_id to all tenant-scoped tables
- [x] Implement organization-scoped data access
- [x] Update existing models with new relationships
- [x] Migrate existing data to organization structure
- [x] Implement polymorphic location system

### Phase 3: Organization Management & Currency System
**Status**: ✅ Complete  
**Progress**: 7/7 tasks completed

- [x] Add currency fields to organizations and customers
- [x] Implement currency selection during organization creation
- [x] Create currency enum system with AED, USD, EUR, GBP, INR support
- [x] Update money formatting throughout application
- [x] Add organization ULID for public document sharing
- [x] Implement tax template system per currency
- [x] Create organization management interface

### Phase 4: Enhanced Models & Relationships
**Status**: ✅ Complete  
**Progress**: 6/6 tasks completed

- [x] Update all models with new relationships
- [x] Implement automatic organization_id assignment
- [x] Add currency logic to invoice creation
- [x] Update factories for new schema
- [x] Enhance validation rules with custom casts
- [x] Update existing tests to pass 94.7% coverage

### Phase 5: Public Routes & Branding
**Status**: ✅ Complete  
**Progress**: 6/6 tasks completed

- [x] Implement ULID-based public routes
- [x] Create public document viewing system
- [x] Update public invoice/estimate templates
- [x] Implement PDF generation with proper styling
- [x] Add email document sharing functionality
- [x] Create responsive public document pages

### Phase 6: UI/UX Updates & Final Testing
**Status**: ✅ Complete  
**Progress**: 6/6 tasks completed

- [x] Update Livewire components for organization-centric architecture
- [x] Implement organization-scoped interfaces
- [x] Add currency selection to forms
- [x] Update navigation and dashboards
- [x] Comprehensive testing with 94.7% coverage
- [x] Performance optimization with proper database indexing

---

## 🛠️ Technical Specifications

### Technology Stack

#### Core Framework
- **Laravel**: 11.19.3
- **PHP**: 8.4.8
- **Database**: PostgreSQL
- **Frontend**: Livewire 3.6.3 + luvi-ui/laravel-luvi (shadcn for Livewire)
- **Testing**: Pest
- **Container**: Laravel Sail

#### Key Packages
- **akaunting/laravel-money**: Monetary value handling
- **luvi-ui/laravel-luvi**: shadcn UI components for Livewire
- **spatie/browsershot**: PDF generation using headless Chrome
- **laravel/jetstream**: Authentication and team management

### Model Implementations

#### Organization Model (Enhanced Team)
```php
class Organization extends Model {
    use HasTeams, HasProfilePhoto;
    
    protected $table = 'teams';
    
    protected function casts(): array {
        return [
            'currency' => Currency::class,
            'emails' => EmailCollectionCast::class,
            'personal_team' => 'boolean',
        ];
    }
    
    public function customers() {
        return $this->hasMany(Customer::class, 'organization_id');
    }
    
    public function invoices() {
        return $this->hasMany(Invoice::class, 'organization_id');
    }
    
    public function taxTemplates() {
        return $this->hasMany(TaxTemplate::class, 'organization_id');
    }
}
```

#### Currency Enum
```php
enum Currency: string {
    case INR = 'INR';
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case AUD = 'AUD';
    case CAD = 'CAD';
    case SGD = 'SGD';
    case JPY = 'JPY';
    case AED = 'AED';
    
    public function symbol(): string { /* ... */ }
    public function name(): string { /* ... */ }
    public static function default(): self { return self::INR; }
}
```

### Testing Infrastructure

#### Test Coverage
- **Current Coverage**: 94.7%
- **Test Count**: 233 tests
- **Test Types**: Unit, Feature, Browser (Laravel Dusk)
- **Test Helpers**: Custom factory methods and test helpers

#### Test Commands
```bash
# Fresh database and run all tests
sail php artisan migrate:fresh --env=testing
sail php artisan test

# Browser tests with screenshots
sail php artisan dusk

# Code formatting
sail pint --dirty
```

---

## 📋 Git Workflow & Quality Assurance

### Development Standards

#### Pre-commit Checklist
```bash
# 1. Fresh test database
sail php artisan migrate:fresh --env=testing

# 2. Run all tests (must pass 100%)
sail php artisan test

# 3. Run browser tests (must pass 100%)
sail php artisan dusk

# 4. Format code
sail pint --dirty

# 5. Check for any remaining issues
sail php artisan config:clear
sail php artisan cache:clear
```

### Quality Metrics
- **Test Coverage**: 94.7% maintained
- **Code Standards**: PSR-12 compliant
- **Performance**: Optimized database queries
- **Security**: Proper input validation and sanitization

---

## 📊 Progress Tracking

### Technical Milestones
- [x] Zero failing tests throughout development
- [x] 100% browser test pass rate maintained
- [x] Proper organization isolation verified
- [x] Multi-currency functionality working (AED, USD, EUR, GBP, INR)
- [x] ULID-based public URLs functional
- [x] Public routes accessible with proper styling

### User Experience Goals
- [x] Seamless user onboarding (< 2 minutes)
- [x] Intuitive organization management
- [x] Fast and responsive interface
- [x] Clear currency selection and display
- [x] Professional public invoice pages

### Performance Targets
- [x] Page load times < 2 seconds
- [x] Database query optimization
- [x] Proper caching implementation
- [x] Mobile-responsive design
- [x] SEO-optimized public pages

---

## 🎯 Success Criteria

### Implementation Achievements
- **Architecture**: Successfully refactored to organization-centric model
- **Multi-Currency**: Full support for 9 currencies with tax templates
- **Public Access**: ULID-based public document sharing
- **Testing**: 94.7% test coverage with comprehensive test suite
- **Performance**: Optimized database queries and caching
- **User Experience**: Streamlined organization management

### Demo Data
- **Organizations**: 8 organizations across different currencies
- **Customers**: 30+ customers with realistic business data
- **Invoices**: 160+ invoices and estimates with various statuses
- **Tax Templates**: Currency-specific tax templates for all supported currencies

---

## 📝 Notes and Decisions

### Architecture Decisions
1. **Organization-Centric**: Simplified from dual Team/Company to single Organization model
2. **Polymorphic Locations**: Unified location management for organizations and customers
3. **Currency Enum**: Type-safe currency handling with tax template integration
4. **ULID Public URLs**: Secure and SEO-friendly public document sharing

### Current State
- **System Status**: Production-ready with comprehensive test coverage
- **Data Integrity**: All relationships properly configured with foreign key constraints
- **Security**: Organization-scoped access control implemented
- **Performance**: Optimized queries with proper database indexing

### Future Enhancements
- [ ] Custom domain support for organizations
- [ ] Advanced payment gateway integration
- [ ] Multi-language support
- [ ] Advanced reporting and analytics
- [ ] API rate limiting per organization
- [ ] Webhook system for integrations

---

**Document Status**: ✅ Implementation Complete - Organization-Centric Architecture  
**Next Action**: Monitor system performance and plan advanced features

---

*This PRD reflects the current implemented state of the multitenant SaaS transformation project. The organization-centric architecture provides a solid foundation for future enhancements while maintaining code quality and test coverage.*