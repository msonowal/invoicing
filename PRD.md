# Product Requirements Document (PRD)
## Multitenant SaaS Invoicing Platform

### Document Version: 1.0
### Last Updated: 2025-07-07
### Status: 🚧 In Development

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
- Register and create teams for collaboration
- Manage multiple business entities (companies)
- Create and manage customers with currency preferences
- Generate invoices and estimates with custom branding
- Share public invoices with SEO-friendly URLs

### Business Objectives
- **Market Expansion**: Enable multiple businesses to use the platform
- **Revenue Growth**: SaaS subscription model with per-company pricing
- **User Experience**: Seamless onboarding and multi-company management
- **Brand Flexibility**: Custom URL handles and branding per company
- **Global Reach**: Multi-currency support for international businesses

### Success Metrics
- [ ] User registration and team creation flow (< 2 minutes)
- [ ] Company onboarding completion rate (> 90%)
- [ ] Multi-currency invoice generation accuracy (100%)
- [ ] Public URL accessibility and SEO performance
- [ ] Test coverage maintenance (> 90%)

---

## 🔍 Current State Analysis

### Existing Architecture (Single-Tenant)
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Company   │───▶│  Customer   │───▶│   Invoice   │
│ (Single)    │    │ (Multiple)  │    │ (Multiple)  │
└─────────────┘    └─────────────┘    └─────────────┘
```

### Target Architecture (Multitenant)
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    User     │───▶│    Team     │───▶│   Company   │───▶│  Customer   │
│             │    │ (Jetstream) │    │ (Business)  │    │ (Scoped)    │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
                                           │
                                           ▼
                                      ┌─────────────┐
                                      │   Invoice   │
                                      │ (Scoped)    │
                                      └─────────────┘
```

### Migration Strategy
- **Zero Downtime**: Gradual migration with feature flags
- **Data Preservation**: Existing data becomes first company of first team
- **Backward Compatibility**: Maintain existing API endpoints during transition

---

## 🏗️ Architecture Overview

### Two-Layer Architecture

#### Layer 1: User Management (Jetstream Teams)
- **Purpose**: User collaboration, permissions, and access control
- **Components**: Users, Teams, Roles, Invitations
- **Features**: Team creation, member management, role-based access
- **Example**: "Acme Holdings Team" with Owner, Admin, Member roles

#### Layer 2: Business Entities (Companies)
- **Purpose**: Actual invoicing businesses with customer and invoice management
- **Components**: Companies, Customers, Invoices, Locations
- **Features**: Multi-company management, custom branding, currency settings
- **Example**: "Acme Web Services", "Acme Consulting", "Acme Products"

### Relationship Flow
```
User → signs up → creates Team → manages Companies → serves Customers → issues Invoices
```

### Key Architectural Decisions
- **Team ≠ Company**: Teams can manage multiple companies (flexible approach)
- **Tenant Isolation**: Global scopes ensure data security per company
- **Context Management**: Session-based company switching
- **Public Access**: Company-scoped public URLs with custom branding

---

## 🗄️ Database Schema

### Enhanced Schema with Multitenant Support

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
    current_team_id BIGINT, -- Selected team context
    profile_photo_path VARCHAR(2048),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (current_team_id) REFERENCES teams(id)
)

teams (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL, -- Team owner
    name VARCHAR(255) NOT NULL,
    personal_team BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)

team_user (
    id BIGINT PRIMARY KEY,
    team_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    role VARCHAR(255) NOT NULL, -- owner, admin, member
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_team_user (team_id, user_id)
)

team_invitations (
    id BIGINT PRIMARY KEY,
    team_id BIGINT NOT NULL,
    email VARCHAR(255) NOT NULL,
    role VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
)

-- ========================================
-- BUSINESS TABLES (Enhanced)
-- ========================================

companies (
    id BIGINT PRIMARY KEY,
    team_id BIGINT NOT NULL, -- Belongs to team
    ulid VARCHAR(26) UNIQUE NOT NULL, -- System identifier
    url_handle VARCHAR(50) UNIQUE, -- Custom SEO handle (3-50 chars)
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(255),
    emails JSON, -- EmailCollection cast
    default_currency CHAR(3) NOT NULL DEFAULT 'USD', -- ISO 4217 currency code
    primary_location_id BIGINT,
    is_active BOOLEAN DEFAULT TRUE,
    settings JSON, -- Company-specific settings
    public_branding JSON, -- Custom branding for public pages
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    INDEX idx_company_ulid (ulid),
    INDEX idx_company_handle (url_handle),
    INDEX idx_team_companies (team_id)
)

customers (
    id BIGINT PRIMARY KEY,
    company_id BIGINT NOT NULL, -- Tenant isolation
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(255),
    emails JSON, -- EmailCollection cast
    preferred_currency CHAR(3), -- Optional customer currency preference
    primary_location_id BIGINT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company_customers (company_id)
)

locations (
    id BIGINT PRIMARY KEY,
    locatable_id BIGINT NOT NULL, -- Polymorphic: Company or Customer
    locatable_type VARCHAR(255) NOT NULL,
    company_id BIGINT NOT NULL, -- Tenant isolation
    location_name VARCHAR(255),
    address_line_1 VARCHAR(255),
    address_line_2 VARCHAR(255),
    city VARCHAR(255),
    state VARCHAR(255),
    country VARCHAR(255),
    postal_code VARCHAR(255),
    gstin VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_locatable (locatable_type, locatable_id),
    INDEX idx_company_locations (company_id)
)

invoices (
    id BIGINT PRIMARY KEY,
    company_id BIGINT NOT NULL, -- Tenant isolation
    customer_id BIGINT NOT NULL,
    ulid VARCHAR(26) UNIQUE NOT NULL, -- Public identifier
    type ENUM('invoice', 'estimate') NOT NULL,
    status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    invoice_number VARCHAR(255),
    currency CHAR(3) NOT NULL, -- Invoice currency (from customer or company)
    company_location_id BIGINT,
    customer_location_id BIGINT,
    issue_date DATE,
    due_date DATE,
    subtotal BIGINT NOT NULL DEFAULT 0, -- In smallest currency unit
    tax BIGINT NOT NULL DEFAULT 0,
    total BIGINT NOT NULL DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (company_location_id) REFERENCES locations(id),
    FOREIGN KEY (customer_location_id) REFERENCES locations(id),
    INDEX idx_company_invoices (company_id),
    INDEX idx_public_ulid (ulid),
    INDEX idx_invoice_currency (currency)
)

invoice_items (
    id BIGINT PRIMARY KEY,
    invoice_id BIGINT NOT NULL,
    company_id BIGINT NOT NULL, -- Tenant isolation
    description TEXT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price BIGINT NOT NULL, -- In smallest currency unit
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    line_total BIGINT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company_items (company_id)
)

-- ========================================
-- SESSION MANAGEMENT
-- ========================================

sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload LONGTEXT,
    last_activity INTEGER,
    current_company_id BIGINT, -- Selected company context
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
)
```

---

## 💱 Multi-Currency System

### Currency Architecture

#### Company Currency Settings
- **Default Currency**: Set during company onboarding (mandatory)
- **Supported Currencies**: USD, EUR, GBP, INR, CAD, AUD, JPY, etc.
- **Storage**: ISO 4217 3-character currency codes
- **Validation**: Against predefined currency list

#### Customer Currency Preferences
- **Optional Setting**: Customers can have preferred currencies
- **Inheritance**: Falls back to company default if not set
- **Flexibility**: Can be different from company currency

#### Invoice Currency Logic
```php
// Currency determination priority:
1. Customer preferred_currency (if set)
2. Company default_currency (fallback)
3. System default 'USD' (ultimate fallback)
```

### Implementation Details

#### Database Storage
- **Monetary Values**: Stored as integers in smallest currency unit (cents/paise)
- **Currency Code**: Stored separately for each invoice
- **Precision**: Maintains accuracy for financial calculations

#### Money Formatting
```php
// Using laravel-money package with dynamic currencies
money($amount, $currency)->format() // ₹1,000.00 for INR
money($amount, $currency)->formatWithCode() // INR 1,000.00
```

#### Currency Configuration
```php
// config/currencies.php
return [
    'supported' => [
        'USD' => ['symbol' => '$', 'name' => 'US Dollar', 'decimals' => 2],
        'EUR' => ['symbol' => '€', 'name' => 'Euro', 'decimals' => 2],
        'GBP' => ['symbol' => '£', 'name' => 'British Pound', 'decimals' => 2],
        'INR' => ['symbol' => '₹', 'name' => 'Indian Rupee', 'decimals' => 2],
        'JPY' => ['symbol' => '¥', 'name' => 'Japanese Yen', 'decimals' => 0],
    ],
    'default' => 'USD',
];
```

### User Experience Flow

#### Company Onboarding
1. **Currency Selection**: Required step during company creation
2. **Regional Defaults**: Pre-select based on user's location
3. **Validation**: Ensure currency is supported

#### Customer Management
1. **Optional Currency**: Can be set when creating/editing customers
2. **Visual Indication**: Show customer's preferred currency in lists
3. **Inheritance Display**: Show effective currency (customer or company default)

#### Invoice Creation
1. **Automatic Detection**: Use customer's preferred or company default
2. **Currency Display**: Show selected currency prominently
3. **Immutable**: Currency cannot be changed after invoice creation

---

## 🔗 Custom URL Handle System

### Handle Management Strategy

#### Default Behavior
- **Automatic Generation**: Use company ULID as default handle
- **No User Action Required**: Companies immediately have public URLs
- **Format**: `company.ulid` (e.g., `01HZ8J9K2N3M4P5Q6R7S8T9V0W`)

#### Custom Handle Features
- **One-Time Change**: Users can set custom handle only ONCE per company lifetime
- **Length**: 3-50 characters for optimal SEO and usability
- **Format**: Alphanumeric characters and hyphens only
- **Validation**: Real-time availability checking
- **Uniqueness**: Global uniqueness across all companies

### Validation Rules

#### Character Requirements
```regex
^[a-z0-9-]{3,50}$
```

#### Reserved Words Protection
```php
$reserved = [
    'api', 'admin', 'www', 'mail', 'ftp', 'localhost',
    'invoices', 'estimates', 'dashboard', 'login', 'register',
    'help', 'support', 'contact', 'about', 'terms', 'privacy'
];
```

#### Real-time Validation
- **Availability Check**: AJAX validation during typing
- **Suggestions**: Offer alternatives if desired handle is taken
- **Visual Feedback**: Green/red indicators for availability

### URL Resolution Logic

#### Route Model Binding
```php
// Enhanced route model binding
public function resolveRouteBinding($value, $field = null) {
    return $this->where('url_handle', $value)
                ->orWhere('ulid', $value)
                ->where('is_active', true)
                ->firstOrFail();
}
```

#### URL Generation
```php
// Helper methods
public function getPublicHandle(): string {
    return $this->url_handle ?? $this->ulid;
}

public function getPublicUrl(): string {
    return url("/{$this->getPublicHandle()}");
}
```

### User Interface

#### Handle Management UI
1. **Company Settings**: Dedicated section for URL customization
2. **Availability Checker**: Real-time validation with visual feedback
3. **Preview**: Show how URLs will look with new handle
4. **One-Time Warning**: Clear indication that change is permanent

#### Public URL Display
1. **Dashboard**: Show current public URL prominently
2. **Invoice Sharing**: Use custom handle in shared links
3. **SEO Benefits**: Search engine friendly URLs

---

## 🔒 Tenant Isolation System

### Global Scopes Implementation

#### Company Scope
```php
class CompanyScope implements Scope {
    public function apply(Builder $builder, Model $model) {
        if (auth()->check() && auth()->user()->currentTeam) {
            $companyIds = auth()->user()->currentTeam->companies()->pluck('id');
            
            if ($companyIds->isNotEmpty()) {
                $builder->whereIn('company_id', $companyIds);
            } else {
                // No companies - return empty results
                $builder->whereRaw('1 = 0');
            }
        }
    }
}
```

#### Model Integration
```php
// Applied to all tenant-scoped models
protected static function booted() {
    static::addGlobalScope(new CompanyScope());
}
```

### Context Management

#### Middleware Stack
```php
Route::middleware([
    'auth',
    EnsureTeamContext::class,
    EnsureCompanyContext::class
])->group(function () {
    // Protected application routes
});
```

#### Context Switching
```php
class CompanyContext {
    public static function current(): ?Company {
        return auth()->user()?->currentTeam?->companies()
            ->find(session('current_company_id'));
    }
    
    public static function switch(Company $company) {
        if (!auth()->user()->hasAccessToCompany($company)) {
            abort(403);
        }
        session(['current_company_id' => $company->id]);
    }
}
```

### Security Policies

#### Company Access Control
```php
class CompanyPolicy {
    public function view(User $user, Company $company) {
        return $user->currentTeam->companies()
                   ->where('id', $company->id)
                   ->exists();
    }
    
    public function update(User $user, Company $company) {
        return $this->view($user, $company) &&
               $user->hasTeamRole($user->currentTeam, 'admin');
    }
}
```

#### Data Access Validation
- **Query Filtering**: Automatic company_id filtering via global scopes
- **Route Protection**: Middleware ensures valid team/company context
- **Policy Enforcement**: Laravel policies for fine-grained control
- **Session Security**: Company context stored securely in session

---

## 🌐 Public Routes Enhancement

### Company-Scoped Public URLs

#### URL Structure
```
https://yourdomain.com/{company_handle}/
https://yourdomain.com/{company_handle}/invoices/{invoice_ulid}
https://yourdomain.com/{company_handle}/estimates/{estimate_ulid}
https://yourdomain.com/{company_handle}/invoices/{invoice_ulid}/pdf
https://yourdomain.com/{company_handle}/contact
```

#### Route Definition
```php
Route::prefix('{company:url_handle}')->group(function () {
    Route::get('/', [PublicCompanyController::class, 'show']);
    Route::get('/invoices/{invoice:ulid}', [PublicViewController::class, 'showInvoice']);
    Route::get('/estimates/{estimate:ulid}', [PublicViewController::class, 'showEstimate']);
    Route::get('/invoices/{invoice:ulid}/pdf', [PublicViewController::class, 'downloadPdf']);
    Route::get('/contact', [PublicCompanyController::class, 'contact']);
});
```

### Custom Branding System

#### Branding Configuration
```php
// Company public_branding JSON structure
{
    "logo_url": "https://...",
    "primary_color": "#3B82F6",
    "secondary_color": "#6B7280",
    "font_family": "Inter",
    "custom_css": "body { ... }",
    "show_company_info": true,
    "contact_email": "contact@company.com",
    "social_links": {
        "website": "https://...",
        "twitter": "https://...",
        "linkedin": "https://..."
    }
}
```

#### Public Page Templates
- **Responsive Design**: Mobile-first approach
- **Brand Integration**: Custom colors, fonts, and logos
- **SEO Optimization**: Meta tags, structured data
- **Performance**: Optimized loading and caching

### Legacy URL Support

#### Automatic Redirects
```php
// Old format: /invoices/{ulid}
// New format: /{company_handle}/invoices/{ulid}
Route::get('/invoices/{invoice:ulid}', function (Invoice $invoice) {
    return redirect()->route('invoices.public', [
        'company' => $invoice->company->getPublicHandle(),
        'invoice' => $invoice->ulid
    ], 301);
});
```

---

## 🚀 Implementation Phases

### Phase 1: Jetstream Setup & Authentication ⏳
**Estimated Duration**: 3-5 days

#### Tasks
- [ ] Install and configure Laravel Jetstream
- [ ] Set up team-based authentication
- [ ] Create user registration and login flows
- [ ] Implement team creation and management
- [ ] Add team member invitation system
- [ ] Configure role-based permissions

#### Deliverables
- Working authentication system
- Team management interface
- User onboarding flow
- Role-based access control

#### Testing Requirements
- [ ] User registration and email verification
- [ ] Team creation and member management
- [ ] Permission enforcement across roles
- [ ] Session management and security

### Phase 2: Tenant Architecture & Global Scopes ⏳
**Estimated Duration**: 4-6 days

#### Tasks
- [ ] Add team_id to companies table
- [ ] Add company_id to all tenant-scoped tables
- [ ] Implement global scopes for tenant isolation
- [ ] Create context management middleware
- [ ] Update existing models with new relationships
- [ ] Migrate existing data to first team/company

#### Deliverables
- Tenant-isolated data access
- Context management system
- Updated model relationships
- Data migration scripts

#### Testing Requirements
- [ ] Tenant isolation verification
- [ ] Cross-tenant data access prevention
- [ ] Context switching functionality
- [ ] Global scope effectiveness

### Phase 3: Company Management & Currency System ⏳
**Estimated Duration**: 5-7 days

#### Tasks
- [ ] Add currency fields to companies and customers
- [ ] Implement currency selection during onboarding
- [ ] Create currency configuration system
- [ ] Update money formatting throughout application
- [ ] Add company ULID and url_handle fields
- [ ] Implement custom handle validation and management
- [ ] Create company settings interface

#### Deliverables
- Multi-currency support
- Custom URL handle system
- Enhanced company management
- Currency-aware invoice generation

#### Testing Requirements
- [ ] Currency selection and validation
- [ ] Money formatting accuracy
- [ ] Custom handle uniqueness and validation
- [ ] Company settings management

### Phase 4: Enhanced Models & Relationships ⏳
**Estimated Duration**: 3-4 days

#### Tasks
- [ ] Update all models with new relationships
- [ ] Implement automatic company_id assignment
- [ ] Add currency logic to invoice creation
- [ ] Update factories for new schema
- [ ] Enhance validation rules
- [ ] Update existing tests

#### Deliverables
- Updated model architecture
- Currency-aware business logic
- Enhanced validation system
- Updated test suite

#### Testing Requirements
- [ ] Model relationship integrity
- [ ] Automatic field assignment
- [ ] Currency logic validation
- [ ] Factory compatibility

### Phase 5: Public Routes & Branding ⏳
**Estimated Duration**: 4-5 days

#### Tasks
- [ ] Implement company-scoped public routes
- [ ] Create public branding system
- [ ] Update public invoice/estimate templates
- [ ] Add legacy URL redirects
- [ ] Implement custom branding interface
- [ ] Add public company profile pages

#### Deliverables
- Company-scoped public URLs
- Custom branding system
- Enhanced public templates
- SEO-optimized pages

#### Testing Requirements
- [ ] Public URL accessibility
- [ ] Branding customization
- [ ] Legacy URL redirects
- [ ] SEO meta data generation

### Phase 6: UI/UX Updates & Final Testing ⏳
**Estimated Duration**: 4-6 days

#### Tasks
- [ ] Update Livewire components for multitenancy
- [ ] Implement company switching interface
- [ ] Add currency selection to forms
- [ ] Update navigation and dashboards
- [ ] Comprehensive testing and bug fixes
- [ ] Performance optimization

#### Deliverables
- Updated user interface
- Company switching functionality
- Performance optimizations
- Comprehensive test coverage

#### Testing Requirements
- [ ] Full user journey testing
- [ ] Performance benchmarking
- [ ] Cross-browser compatibility
- [ ] Mobile responsiveness

---

## 🛠️ Technical Specifications

### Required Packages

#### New Dependencies
```bash
composer require laravel/jetstream
composer require livewire/livewire:^3.0
npm install @tailwindcss/forms @tailwindcss/typography
```

#### Configuration Updates
```php
// config/jetstream.php
'features' => [
    Features::termsAndPrivacyPolicy(),
    Features::profilePhotos(),
    Features::api(),
    Features::teams(['invitations' => true]),
    Features::accountDeletion(),
],

// config/currencies.php
return [
    'supported' => [...],
    'default' => 'USD',
    'formatting' => [...],
];
```

### Model Implementations

#### Enhanced Company Model
```php
class Company extends Model {
    use HasFactory;

    protected $fillable = [
        'team_id', 'ulid', 'url_handle', 'name', 'phone', 'emails',
        'default_currency', 'primary_location_id', 'is_active',
        'settings', 'public_branding'
    ];

    protected function casts(): array {
        return [
            'emails' => EmailCollectionCast::class,
            'settings' => 'array',
            'public_branding' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted() {
        static::creating(function ($company) {
            if (!$company->ulid) {
                $company->ulid = (string) Str::ulid();
            }
            if (!$company->team_id) {
                $company->team_id = auth()->user()?->current_team_id;
            }
        });
    }

    // Relationships
    public function team() {
        return $this->belongsTo(Team::class);
    }

    public function customers() {
        return $this->hasMany(Customer::class);
    }

    public function invoices() {
        return $this->hasMany(Invoice::class);
    }

    // URL handling
    public function getPublicHandle(): string {
        return $this->url_handle ?? $this->ulid;
    }

    public function getRouteKeyName(): string {
        return 'url_handle';
    }

    public function resolveRouteBinding($value, $field = null) {
        return $this->where('url_handle', $value)
                    ->orWhere('ulid', $value)
                    ->where('is_active', true)
                    ->firstOrFail();
    }
}
```

### Migration Files

#### Add Team Support to Companies
```php
Schema::table('companies', function (Blueprint $table) {
    $table->foreignId('team_id')->after('id')->constrained()->onDelete('cascade');
    $table->string('ulid', 26)->unique()->after('team_id');
    $table->string('url_handle', 50)->unique()->nullable()->after('ulid');
    $table->char('default_currency', 3)->default('USD')->after('emails');
    $table->json('public_branding')->nullable()->after('settings');
    
    $table->index('team_id');
    $table->index('ulid');
    $table->index('url_handle');
});
```

### Validation Rules

#### Custom Handle Validation
```php
class CustomHandleRule implements Rule {
    public function passes($attribute, $value) {
        // Check format
        if (!preg_match('/^[a-z0-9-]{3,50}$/', $value)) {
            return false;
        }
        
        // Check reserved words
        $reserved = config('companies.reserved_handles');
        if (in_array($value, $reserved)) {
            return false;
        }
        
        // Check uniqueness
        return !Company::where('url_handle', $value)->exists();
    }
    
    public function message() {
        return 'The :attribute must be 3-50 characters, alphanumeric with hyphens only, and not reserved.';
    }
}
```

---

## 📋 Git Workflow & Quality Assurance

### Mandatory Workflow Steps

#### Before Every Commit
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

### Commit Standards

#### Conventional Commit Format
```
feat: add multi-currency support to companies
fix: resolve tenant isolation in customer queries
refactor: simplify custom handle validation logic
test: add comprehensive currency formatting tests
docs: update PRD with implementation progress
```

#### Atomic Commits
- One feature/fix per commit
- Complete and working state after each commit
- Descriptive commit messages
- Reference to PRD sections when applicable

### Branch Strategy

#### Feature Branches
```bash
# Feature branch naming
feature/jetstream-setup
feature/tenant-isolation
feature/multi-currency
feature/custom-handles
feature/public-routes
```

#### Pull Request Requirements
- [ ] All tests passing
- [ ] Code formatted with Laravel Pint
- [ ] PRD updated with progress
- [ ] Documentation updated
- [ ] No breaking changes without migration path

### Quality Gates

#### Code Quality
- [ ] Laravel best practices followed
- [ ] PSR-12 coding standards
- [ ] No code duplication
- [ ] Proper error handling
- [ ] Security best practices

#### Test Coverage
- [ ] Unit tests for all business logic
- [ ] Feature tests for all endpoints
- [ ] Browser tests for critical user flows
- [ ] Minimum 90% code coverage maintained

---

## 📊 Progress Tracking

### Overall Progress: 🟡 Planning Complete - Ready for Implementation

### Phase 1: Jetstream Setup & Authentication
**Status**: ⏳ Pending  
**Progress**: 0/6 tasks completed

- [ ] Install and configure Laravel Jetstream
- [ ] Set up team-based authentication  
- [ ] Create user registration and login flows
- [ ] Implement team creation and management
- [ ] Add team member invitation system
- [ ] Configure role-based permissions

### Phase 2: Tenant Architecture & Global Scopes  
**Status**: ⏳ Pending  
**Progress**: 0/6 tasks completed

- [ ] Add team_id to companies table
- [ ] Add company_id to all tenant-scoped tables
- [ ] Implement global scopes for tenant isolation
- [ ] Create context management middleware
- [ ] Update existing models with new relationships
- [ ] Migrate existing data to first team/company

### Phase 3: Company Management & Currency System
**Status**: ⏳ Pending  
**Progress**: 0/7 tasks completed

- [ ] Add currency fields to companies and customers
- [ ] Implement currency selection during onboarding
- [ ] Create currency configuration system
- [ ] Update money formatting throughout application
- [ ] Add company ULID and url_handle fields
- [ ] Implement custom handle validation and management
- [ ] Create company settings interface

### Phase 4: Enhanced Models & Relationships
**Status**: ⏳ Pending  
**Progress**: 0/6 tasks completed

- [ ] Update all models with new relationships
- [ ] Implement automatic company_id assignment
- [ ] Add currency logic to invoice creation
- [ ] Update factories for new schema
- [ ] Enhance validation rules
- [ ] Update existing tests

### Phase 5: Public Routes & Branding
**Status**: ⏳ Pending  
**Progress**: 0/6 tasks completed

- [ ] Implement company-scoped public routes
- [ ] Create public branding system
- [ ] Update public invoice/estimate templates
- [ ] Add legacy URL redirects
- [ ] Implement custom branding interface
- [ ] Add public company profile pages

### Phase 6: UI/UX Updates & Final Testing
**Status**: ⏳ Pending  
**Progress**: 0/6 tasks completed

- [ ] Update Livewire components for multitenancy
- [ ] Implement company switching interface
- [ ] Add currency selection to forms
- [ ] Update navigation and dashboards
- [ ] Comprehensive testing and bug fixes
- [ ] Performance optimization

---

## 🎯 Success Criteria

### Technical Milestones
- [ ] Zero failing tests throughout development
- [ ] 100% browser test pass rate maintained
- [ ] Proper tenant isolation verified
- [ ] Multi-currency functionality working
- [ ] Custom URL handles functional
- [ ] Public routes accessible and branded

### User Experience Goals
- [ ] Seamless user onboarding (< 2 minutes)
- [ ] Intuitive company switching
- [ ] Fast and responsive interface
- [ ] Clear currency selection and display
- [ ] Professional public invoice pages

### Performance Targets
- [ ] Page load times < 2 seconds
- [ ] Database query optimization
- [ ] Proper caching implementation
- [ ] Mobile-responsive design
- [ ] SEO-optimized public pages

---

## 📝 Notes and Decisions

### Architecture Decisions
1. **Team ≠ Company**: Chosen for maximum flexibility in business scenarios
2. **One-time Handle Change**: Prevents URL instability and SEO issues
3. **Currency per Invoice**: Ensures historical accuracy and flexibility
4. **Global Scopes**: Automatic tenant isolation for security

### Future Enhancements
- [ ] Custom domain support for companies
- [ ] Advanced payment gateway integration
- [ ] Multi-language support
- [ ] Advanced reporting and analytics
- [ ] API rate limiting per company
- [ ] Webhook system for integrations

### Risk Mitigation
- **Data Migration**: Comprehensive backup and rollback procedures
- **Performance**: Query optimization and caching strategies
- **Security**: Regular security audits and penetration testing
- **Scalability**: Database indexing and query optimization

---

**Document Status**: ✅ Complete and Ready for Implementation  
**Next Action**: Begin Phase 1 implementation with Jetstream setup

---

*This PRD serves as the single source of truth for the multitenant SaaS transformation project. All team members should refer to this document for specifications, progress tracking, and decision history.*