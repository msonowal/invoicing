# Database Schema Optimization Plan - Integer-Only Financial Data

## Overview
Convert all decimal/float financial fields to integers using smallest denominations (cents, basis points, micro-units) for 100% precision. Keep ALL business logic and calculations in integers, only format for UI/presentation layer.

## Phase 1: Eliminate All Decimal/Float Fields (High Priority)
- [ ] Convert tax_templates.rate to unsignedInteger (basis points: 18.000% = 18000)
- [ ] Convert invoice_items.tax_rate to unsignedInteger (basis points: 18.00% = 1800)  
- [ ] Convert invoices.exchange_rate to unsignedBigInteger (micro-units: 1.234567 = 1234567)

## Phase 2: Optimize Monetary Integer Fields (High Priority)
- [ ] Convert invoices monetary fields (subtotal, tax, total) to unsignedBigInteger
- [ ] Convert invoice_items.unit_price to unsignedBigInteger
- [ ] Convert invoice_items.quantity to unsignedInteger

## Phase 3: Fixed-Length Field Optimization (Medium Priority)
- [ ] Convert invoices.currency from string(3) to char(3)
- [ ] Convert teams.currency from string(3) to char(3)
- [ ] Convert tax_templates.country_code from string(2) to char(2)
- [ ] Convert personal_access_tokens.token from string(64) to char(64)

## Phase 4: Business Logic - Keep All Calculations in Integers (Critical)
- [ ] Update InvoiceCalculator service to work purely with integers (cents, basis points)
- [ ] Update TaxTemplate model - store/retrieve basis points, never convert internally
- [ ] Update InvoiceItem model - tax calculations in basis points only
- [ ] Update Invoice model - exchange rate calculations in micro-units only
- [ ] Remove any decimal/float arithmetic from business logic
- [ ] Ensure all mathematical operations use integer arithmetic only

## Phase 5: Presentation Layer - Format Integers for Display Only (Critical)
- [ ] Update Money formatting methods to convert integers to display strings
- [ ] Update Livewire components to format values ONLY for display
- [ ] Update Blade templates to show formatted values (never raw integers)
- [ ] Add basis points to percentage formatters (18000 → "18.00%")
- [ ] Add micro-units to exchange rate formatters (1234567 → "1.234567")
- [ ] Ensure form inputs convert user input back to integers

## Phase 6: Data Input/Output Boundaries (Critical)
- [ ] Update API endpoints to accept user values and convert to integers
- [ ] Update form validation to work with user-friendly values but store integers
- [ ] Update CSV/Excel import to convert formatted values to integers
- [ ] Update PDF generation to format integers for display
- [ ] Ensure database seeds use integer values directly

## Phase 7: Testing & Validation (Critical)
- [ ] Update all factories to generate integer values in correct scales
- [ ] Update all tests to work with integers internally, format for assertions
- [ ] Add tests for basis points calculations (18000 basis points = 18%)
- [ ] Add tests for micro-unit calculations (1234567 = 1.234567 rate)
- [ ] Test edge cases with large integer values
- [ ] Verify no floating point arithmetic anywhere in codebase

## Phase 8: Code Quality & Consistency (Medium Priority)
- [ ] Add helper methods for basis points conversions
- [ ] Add helper methods for micro-unit conversions  
- [ ] Create constants for conversion factors (BASIS_POINTS_DIVISOR = 10000)
- [ ] Add documentation for integer-only financial architecture
- [ ] Code review to ensure no decimal types introduced

## Implementation Rules:
1. **Database**: Only integers (unsigned where appropriate)
2. **Business Logic**: Only integer arithmetic, no conversions
3. **Presentation**: Format integers to user-friendly display only
4. **Input**: Convert user input to integers at boundary
5. **Never**: Use float/decimal in calculations or business logic

## Data Scale Standards:
- **Money**: Cents (12345 = $123.45)
- **Tax Rates**: Basis points (1800 = 18.00%)  
- **Exchange Rates**: Micro-units (1234567 = 1.234567)
- **Quantities**: Whole integers (no conversion needed)

## Expected Benefits:
- **Precision**: 100% accurate financial calculations (no floating point errors)
- **Performance**: Integer arithmetic faster than decimal operations  
- **Storage**: Smaller footprint for integers vs decimals
- **Consistency**: All financial data uses same integer-based approach
- **Future-proof**: BigInteger supports enterprise-scale transactions

## Current Status:
- Phase 1: Not started
- Phase 2: Not started  
- Phase 3: Not started
- Phase 4: Not started
- Phase 5: Not started
- Phase 6: Not started
- Phase 7: Not started
- Phase 8: Not started

## Next Steps:
1. Start with Phase 1 database schema changes
2. Update business logic to use integers only
3. Update presentation layer for proper formatting
4. Comprehensive testing and validation