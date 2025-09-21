# Invoice Management System

## Overview

The Invoice Management System is a comprehensive module for generating, managing, and tracking invoices within the MetaSoft LetterHead application. It integrates seamlessly with the existing company management system and provides a complete workflow from invoice creation to payment tracking.

## Core Features

### 1. Invoice Creation & Management
- Create invoices with multiple line items
- Associate invoices with existing companies
- Manage client/recipient information
- Automatic total calculations including tax and discounts
- Invoice status tracking (draft, sent, paid, overdue, cancelled)

### 2. Client Management
- Store client company information separate from issuing companies
- Multiple contact methods (phone, email, website)
- Multiple payment options (MPESA, Bank Transfer)
- Reusable client profiles for future invoices

### 3. Financial Calculations
- Line-item level tax and discount calculations
- Automatic subtotal, tax, and grand total computation
- Multi-currency support (default: KSH)
- Payment tracking with reference numbers

### 4. Document Generation
- PDF invoice generation matching the provided design template
- Company branding integration (logos, letterheads)
- Professional invoice layout with terms and conditions

## Database Schema

### Invoices Table
```sql
- id (Primary Key)
- user_id (Foreign Key to users)
- invoice_number (Unique identifier, e.g., "MSDI 2588")
- invoice_date, due_date
- company_id (Foreign Key to companies - issuing company)
- invoice_to_id (Foreign Key to invoice_tos - recipient client)
- sub_total, tax_amount, discount_amount, balance, grand_total
- currency (default: KSH)
- status (draft|sent|paid|overdue|cancelled)
- notes, internal_notes
- payment details (paid_at, payment_method, payment_reference)
- timestamps, soft_deletes
```

### Invoice Items Table
```sql
- id (Primary Key)
- invoice_id (Foreign Key to invoices)
- service_name, description, period
- quantity, unit, unit_price, line_total
- tax_rate, tax_amount
- discount_rate, discount_amount
- sort_order
- metadata (JSON)
- timestamps
```

### Invoice Tos (Clients) Table
```sql
- id (Primary Key)
- user_id (Foreign Key to users)
- company_name, company_address
- primary_phone, secondary_phone, email, website
- mpesa_account, mpesa_holder_name
- bank_name, bank_account, bank_holder_name
- additional_notes
- timestamps, soft_deletes
```

## Model Relationships

### Invoice Model
- `belongsTo(User::class)` - Invoice creator/owner
- `belongsTo(Company::class)` - Issuing company
- `belongsTo(InvoiceTo::class, 'invoice_to_id')` - Client/recipient
- `hasMany(InvoiceItem::class)` - Invoice line items

### InvoiceItem Model  
- `belongsTo(Invoice::class)` - Parent invoice

### InvoiceTo Model
- `belongsTo(User::class)` - Client record owner
- `hasMany(Invoice::class, 'invoice_to_id')` - Invoices sent to this client

## Key Business Logic

### Automatic Calculations
- **Line Item Totals**: `(quantity × unit_price) - discount + tax`
- **Invoice Totals**: Automatically recalculated when items change
- **Tax Application**: Applied after discounts
- **Currency Formatting**: Consistent formatting across the system

### Invoice Status Workflow
1. **Draft** - Initial creation, editable
2. **Sent** - Issued to client, limited editing
3. **Paid** - Payment received and recorded
4. **Overdue** - Past due date without payment
5. **Cancelled** - Voided invoice

### Payment Tracking
- Multiple payment methods (MPESA, Bank Transfer, etc.)
- Payment reference/transaction ID storage
- Automatic status updates when paid
- Balance tracking for partial payments

## Livewire Component Architecture

### Main Component: `InvoiceManagement.php`
**Purpose**: Primary interface for invoice CRUD operations

**Key Properties**:
```php
public $invoices;           // Collection of user's invoices
public $selectedInvoice;    // Currently selected invoice for editing
public $clients;            // Available clients for selection
public $companies;          // User's companies
public $showModal = false;  // Modal visibility state
public $modalMode = 'create'; // create|edit|view
```

**Key Methods**:
- `mount()` - Initialize data and load user's invoices
- `createInvoice()` - Start new invoice creation
- `editInvoice($invoiceId)` - Load invoice for editing
- `viewInvoice($invoiceId)` - Display invoice details
- `saveInvoice()` - Save invoice (create/update)
- `deleteInvoice($invoiceId)` - Soft delete invoice
- `generatePDF($invoiceId)` - Generate invoice PDF
- `markAsPaid($invoiceId)` - Update invoice status to paid

### Sub-Component: `InvoiceForm.php`
**Purpose**: Invoice creation/editing form with line items

**Key Properties**:
```php
public $invoice;            // Invoice model instance
public $items = [];         // Array of invoice items
public $selectedClient;     // Selected client ID
public $newClient = [];     // New client data if creating
public $totals = [];        // Calculated totals
```

**Key Methods**:
- `addItem()` - Add new line item
- `removeItem($index)` - Remove line item
- `updateTotals()` - Recalculate all totals
- `saveInvoice()` - Validate and save complete invoice
- `selectClient($clientId)` - Load client data
- `createNewClient()` - Create client and associate

### Sub-Component: `ClientManagement.php`
**Purpose**: Manage invoice recipients/clients

**Key Properties**:
```php
public $clients;            // User's clients
public $selectedClient;     // Client being edited
public $showClientModal = false;
public $clientForm = [];    // Client form data
```

**Key Methods**:
- `createClient()` - Create new client
- `editClient($clientId)` - Edit existing client
- `saveClient()` - Save client data
- `deleteClient($clientId)` - Soft delete client

## User Interface Design

### Invoice List View
- Table format with key invoice details
- Status badges with appropriate colors
- Action buttons (View, Edit, PDF, Delete)
- Search and filter capabilities
- Pagination for large datasets

### Invoice Form Modal
- Step-by-step wizard or single comprehensive form
- Real-time total calculations
- Dynamic line item management
- Client selection/creation interface
- Validation feedback

### Invoice Preview
- Matches the provided HTML template design
- Company header with logo and branding
- Professional invoice layout
- Clear payment instructions
- Terms and conditions section

## Integration Points

### Company System Integration
- Uses existing Company model for issuer information
- Leverages company logos and branding
- Inherits user permissions and ownership

### File Management Integration
- PDF generation and storage
- Company logo integration
- Potential invoice template customization

### Authentication Integration
- User-based invoice ownership
- Permission-based access control
- Multi-tenant data isolation

## Technical Requirements

### Laravel/PHP Requirements
- Laravel 12.x framework
- PHP 8.2+ for modern syntax support
- Eloquent ORM for database operations
- Form Request validation classes

### Livewire 3.x Features
- Real-time form validation
- Dynamic component updates
- Event-driven architecture
- Modal management
- File upload handling

### Frontend Requirements
- Bootstrap 5 styling (consistent with existing app)
- Alpine.js for enhanced interactions
- Responsive design for mobile/desktop
- Print-friendly PDF generation

### Database Requirements
- Foreign key constraints for data integrity
- Indexes for performance optimization
- Soft deletes for audit trails
- JSON columns for flexible metadata

## Security Considerations

### Data Protection
- User-based data isolation (all queries filtered by user_id)
- Soft deletes for audit trails
- Input validation and sanitization
- SQL injection prevention via Eloquent

### Access Control
- Invoice ownership validation
- Client data privacy (user can only see their clients)
- Invoice modification restrictions based on status
- PDF access control

### Financial Data Security
- Decimal precision for financial calculations
- Currency validation and formatting
- Audit trail for all financial changes
- Secure payment information handling

## Performance Optimizations

### Database Optimization
- Proper indexing on frequently queried fields
- Eager loading to prevent N+1 queries
- Pagination for large datasets
- Efficient relationship loading

### Frontend Optimization
- Lazy loading for modals and forms
- Minimal DOM updates via Livewire
- Optimized PDF generation
- Caching for frequently accessed data

## Future Enhancements

### Potential Features
- Invoice templates and customization
- Recurring invoice automation
- Payment gateway integration
- Email invoice delivery
- Invoice analytics and reporting
- Multi-language support
- Invoice approval workflows

### API Integration Opportunities
- Third-party payment processors
- Accounting software integration
- Email service providers
- SMS notifications for payment reminders

## Testing Strategy

### Unit Tests
- Model relationship tests
- Calculation method tests
- Validation rule tests
- Business logic verification

### Feature Tests
- Complete invoice creation workflow
- PDF generation functionality
- Payment status updates
- User access control

### Browser Tests
- End-to-end invoice creation
- Form validation behavior
- Modal interactions
- Responsive design testing

This comprehensive documentation serves as the blueprint for implementing the Invoice Management System within the MetaSoft LetterHead application, ensuring seamless integration with existing features while providing robust invoice management capabilities.