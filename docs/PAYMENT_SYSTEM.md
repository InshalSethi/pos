# Payment System Documentation

## Overview

The Payment System is a comprehensive module for managing all payment-related transactions in the POS application. It provides a flexible framework to handle various types of payments including supplier payments, expense payments, salary payments, sale return payments, and purchase invoice payments.

## Features

### Core Features
- **Multiple Payment Types**: Support for supplier payments, expense payments, salary payments, sale return payments, purchase invoice payments, and other payments
- **Approval Workflow**: Draft → Pending → Approved → Paid status flow
- **Accounting Integration**: Full double-entry bookkeeping with automatic journal entries
- **Bank Account Integration**: Links payments to specific bank accounts with transaction tracking
- **Permission-Based Access**: Role-based permissions for view, create, edit, delete, approve, and pay operations

### Payment Types Supported
1. **Supplier Payment**: Payments made to suppliers for goods/services
2. **Expense Payment**: Payments for approved expenses
3. **Salary Payment**: Employee salary and wage payments
4. **Sale Return Payment**: Refunds for returned sales
5. **Purchase Invoice Payment**: Payments for purchase invoices
6. **Other Payment**: Miscellaneous payments

## Database Structure

### Payments Table
- `id`: Primary key
- `payment_number`: Unique payment identifier (auto-generated)
- `payment_type`: Type of payment (enum)
- `reference_type` & `reference_id`: Polymorphic reference to related entity
- `amount`: Payment amount (decimal 15,2)
- `payment_date`: Date of payment
- `payment_method`: Method used (cash, bank_transfer, check, card)
- `reference_number`: External reference number
- `description`: Payment description
- `notes`: Additional notes
- `bank_account_id`: Associated bank account
- `payee_type` & `payee_id`: Polymorphic payee reference
- `payee_name`: Payee name (backup/manual entry)
- `status`: Payment status (draft, pending, approved, paid, cancelled)
- Approval fields: `approved_by`, `approved_at`, `approval_notes`
- Payment fields: `paid_by`, `paid_at`
- Accounting fields: `journal_entry_id`, `bank_transaction_id`
- `additional_data`: JSON field for type-specific data
- Standard timestamps and user tracking

## API Endpoints

### Payment Management
- `GET /api/payments` - List payments with filtering and pagination
- `POST /api/payments` - Create new payment
- `GET /api/payments/{id}` - Get payment details
- `PUT /api/payments/{id}` - Update payment
- `DELETE /api/payments/{id}` - Delete payment

### Payment Actions
- `POST /api/payments/{id}/approve` - Approve payment
- `POST /api/payments/{id}/mark-as-paid` - Mark payment as paid
- `POST /api/payments/{id}/cancel` - Cancel payment

### Utility Endpoints
- `GET /api/payments-statistics` - Get payment statistics
- `GET /api/payment-options` - Get dropdown options for forms

## Permissions

The system uses the following permissions:
- `payments.view` - View payments
- `payments.create` - Create new payments
- `payments.edit` - Edit existing payments
- `payments.delete` - Delete payments
- `payments.approve` - Approve payments
- `payments.pay` - Mark payments as paid

## Accounting Integration

### Journal Entries
Each payment automatically creates journal entries based on payment type:

#### Supplier Payment
- **Debit**: Accounts Payable
- **Credit**: Bank Account

#### Expense Payment
- **Debit**: Accounts Payable (Expense)
- **Credit**: Bank Account

#### Salary Payment
- **Debit**: Salary Payable
- **Credit**: Bank Account

#### Sale Return Payment
- **Debit**: Sales Returns
- **Credit**: Bank Account

#### Other Payment
- **Debit**: Miscellaneous Expense
- **Credit**: Bank Account

### Bank Transactions
Each paid payment creates a corresponding bank transaction with:
- Transaction type: Debit
- Running balance calculation
- Partner reference linking

## Frontend Components

### Main Components
1. **Payments.vue** - Main payment list with filtering and actions
2. **PaymentFormModal.vue** - Create/edit payment form
3. **PaymentViewModal.vue** - Payment details view with actions

### Features
- **Advanced Filtering**: By type, status, date range, bank account
- **Search Functionality**: Search by payment number, payee, description
- **Sortable Columns**: Sort by date, amount, status
- **Action Buttons**: Context-sensitive actions based on permissions and status
- **Real-time Updates**: Automatic refresh after actions

## Integration Points

### Expense Integration
- "Create Payment" button in expense details for approved expenses
- Pre-fills payment form with expense data
- Links payment to original expense record

### Dashboard Integration
- Payment statistics cards showing total and pending amounts
- Payment breakdown by type and status
- Quick links to payment management

### Sidebar Navigation
- Permission-based menu item
- Direct access to payment management

## Usage Examples

### Creating a Supplier Payment
1. Navigate to Payments menu
2. Click "New Payment"
3. Select "Supplier Payment" type
4. Choose supplier from dropdown
5. Enter amount and details
6. Select bank account
7. Save as draft or submit for approval

### Approval Workflow
1. Manager reviews pending payments
2. Clicks "Approve" on payment
3. Adds approval notes if needed
4. Payment status changes to "Approved"
5. Accounting entries are created

### Payment Execution
1. Authorized user marks approved payment as "Paid"
2. Bank transaction is created
3. Journal entries are finalized
4. Running balances are updated

## Error Handling

The system includes comprehensive error handling for:
- Invalid payment data
- Insufficient permissions
- Accounting integration failures
- Bank account validation
- Status transition validation

## Security Features

- Permission-based access control
- User tracking for all actions
- Audit trail through journal entries
- Status-based action restrictions
- Input validation and sanitization

## Future Enhancements

Potential future improvements:
- Bulk payment processing
- Payment templates
- Recurring payments
- Payment approval chains
- Integration with external payment gateways
- Advanced reporting and analytics
- Payment reconciliation features

## Installation and Setup

1. Run migration: `php artisan migrate`
2. Seed permissions: `php artisan db:seed --class=PaymentPermissionsSeeder`
3. Assign permissions to roles as needed
4. Configure accounting settings for proper account mapping
5. Set up bank accounts for payment processing

## Testing

The system includes comprehensive tests covering:
- Payment creation and management
- Approval workflow
- Accounting integration
- Permission validation
- API endpoints
- Statistics and reporting

Run tests with: `php artisan test tests/Feature/PaymentTest.php`
