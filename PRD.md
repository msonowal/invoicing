# 🧾 Laravel Invoicing App Plan

## 1. Project Overview

- **Inspiration**: Zoho Books
- **Stack**: Laravel 12, PHP 8.4, PostgreSQL, Livewire 3, Pest
- **UI**: `luvi-ui/laravel-luvi`
- **Packages**: `akaunting/laravel-money`, Yarn 4 (via corepack)
- **Schema**: Polymorphic locations, **JSON column for emails with Value Objects**.
- **Core Features**:
    - Company & Customer CRUD with multiple locations & emails.
    - Unified Estimates & Invoices (differentiated by a `type` flag).
    - **Emailing documents** with secure, public view links.
- **Guidelines**: Atomic, conventional commits; all Pest tests must pass before commit.
- **Out of Scope**: PDF generation, payment tracking, client portal, full recurring invoice automation.

---

## 2. Development Steps

### A. Setup & Installation

- You can run `sail php artisan about` to know about the current environment
- check current default migrations and Run initial database migrations to create the core tables.

### B. Models & Migrations
*(All monetary values stored as integers)* Never use floats in DB

- **`Company` / `Customer`**:
    - `name`, `phone`, `primary_location_id`.
    - `emails` (json column).
- **`Location` (Polymorphic)**: `locatable_id`, `locatable_type`, `name`, `gstin`, `address_line_1`, `city`, `state`, etc.
- **`Currency`**: `code`, `symbol`, `name`, `precision`. **Note**: For this phase, the app will operate with a single default currency. This model is for foundational purposes and future multi-currency support.
- **`Invoice`**: `type` (enum: 'invoice', 'estimate'), `uuid`, `company_location_id`, `customer_location_id`, `invoice_number` (string, unique), `status` (enum: 'draft', 'sent', 'paid', 'void'), `issued_at` (timestamp), `due_at` (timestamp), `subtotal` (integer), `tax` (integer), `total` (integer).
- **`InvoiceItem`**: `invoice_id` (FK), `description`, `quantity`, `unit_price` (integer), `tax_rate` (integer, optional).

### C. Business Logic & Testing

- **Value Objects**: Create an `EmailCollection` Value Object to manage email data (e.g., `["test@test.com"]`).
- **Custom Casts**: Implement an `EmailCollectionCast` to automatically cast the `emails` JSON column to the `EmailCollection` Value Object.
- **Services**: `InvoiceCalculator`, `EstimateToInvoiceConverter`.
- **Mailers**: `DocumentMailer` Mailable class.
- **Pest Tests**: Cover the custom cast, Value Object logic, and all core features.

### D. Livewire UI/UX (using Luvi UI which is shadcn look and feel for livewire) Fetch latest documentation to know about the unknown things

- **Components**:
    - Company/Customer CRUD with a nested component to manage the `emails` collection.
    - A unified wizard for creating Estimates & Invoices.
    - **Email Modal**: To select recipients from the customer's `EmailCollection`.
- **Public View**: Routes (`/invoices/{uuid}` and `/estimates/{uuid}`) to display documents.