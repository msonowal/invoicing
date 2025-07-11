# Monetary Value Integration Plan with akaunting/laravel-money

## Overview
This plan tracks the implementation of proper monetary value handling using the akaunting/laravel-money package to replace hardcoded currency symbols and inconsistent formatting across the application.

## Current State Analysis

### ✅ **What's Working Well:**
- [x] All monetary values stored as integers (cents) in DB - no migration needed
- [x] Proper precision handling with integer arithmetic
- [x] Comprehensive test coverage (94.7%) with integer-based calculations
- [x] Strong architectural foundation with Value Objects (InvoiceTotals)

### ❌ **Critical Issues Found:**
- [ ] **Hardcoded Currency**: All views use hardcoded `₹` symbols regardless of organization currency
- [ ] **Manual Formatting**: 26+ instances of manual `number_format($amount / 100, 2)` division
- [ ] **Inconsistent Display**: Some views don't divide by 100 (formatting bugs)
- [ ] **No Multi-Currency Support**: Organizations have different currencies but views show INR only
- [ ] **Code Duplication**: Same formatting logic repeated across all views

## Implementation Plan

### **Phase 1: Model Enhancement** 
- [ ] Add Money helper methods to Invoice model using akaunting/laravel-money
- [ ] Add Money helper methods to InvoiceItem model for line item formatting  
- [ ] Update InvoiceTotals Value Object with Money formatting methods

### **Phase 2: View Updates**
- [ ] Update Invoice public view - replace hardcoded ₹ symbols with dynamic currency
- [ ] Update Estimate public view - replace hardcoded ₹ symbols with dynamic currency
- [ ] Update InvoiceWizard Livewire component - fix hardcoded ₹ and line 297 inconsistency

### **Phase 3: PDF & Email Templates**
- [ ] Update PDF invoice template - replace hardcoded ₹ symbols with dynamic currency
- [ ] Update PDF estimate template - replace hardcoded ₹ symbols with dynamic currency
- [ ] Update email templates - replace hardcoded ₹ symbols with dynamic currency

### **Phase 4: Testing & Validation**
- [ ] Run all existing tests to ensure no regression
- [ ] Create new tests for multi-currency formatting functionality
- [ ] Test with different organization currencies (USD, EUR, AED, INR)
- [ ] Run Laravel Pint formatting before final commit

## Files to Update

### **Views with Formatting Issues (26+ instances):**
- `/resources/views/public/invoice.blade.php` - 6 hardcoded ₹ symbols
- `/resources/views/public/estimate.blade.php` - 6 hardcoded ₹ symbols  
- `/resources/views/livewire/invoice-wizard.blade.php` - 6 hardcoded ₹ symbols + 1 inconsistency
- `/resources/views/pdf/invoice.blade.php` - 6 hardcoded ₹ symbols
- `/resources/views/pdf/estimate.blade.php` - 6 hardcoded ₹ symbols
- `/resources/views/emails/invoice.blade.php` - 1 hardcoded ₹ symbol
- `/resources/views/emails/estimate.blade.php` - 1 hardcoded ₹ symbol

### **Models to Enhance:**
- `/app/Models/Invoice.php` - Add formatMoney() methods
- `/app/Models/InvoiceItem.php` - Add formatLineTotal() methods  
- `/app/ValueObjects/InvoiceTotals.php` - Add Money formatting methods

## Expected Outcomes
- ✅ **Multi-currency support** across all views and PDFs
- ✅ **Consistent formatting** eliminating hardcoded symbols
- ✅ **Proper internationalization** following currency formatting rules
- ✅ **Code maintainability** through centralized formatting logic
- ✅ **Backward compatibility** with existing integer storage and calculations

## Progress Tracking
- **Started:** [Current Date]
- **Status:** In Progress
- **Next Session:** Continue with Phase 1 - Model Enhancement

## Testing Strategy
- Ensure all 233 existing tests continue to pass
- Test with multi-currency organizations (USD, EUR, AED, INR)
- Verify PDF generation works with new formatting
- Test Livewire components with different currencies

## Rollback Plan
- All changes maintain backward compatibility
- Existing integer storage and calculations remain unchanged
- Can revert view changes if needed without data loss