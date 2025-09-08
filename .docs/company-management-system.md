# Company Management System Documentation

## Table of Contents
1. [Overview](#overview)
2. [Database Schema](#database-schema)
3. [Model Architecture](#model-architecture)
4. [Service Layer](#service-layer)
5. [Business Logic](#business-logic)
6. [Integration Points](#integration-points)
7. [Usage Examples](#usage-examples)
8. [API Reference](#api-reference)
9. [Security Considerations](#security-considerations)
10. [Performance Optimizations](#performance-optimizations)

---

## Overview

The Company Management System provides comprehensive CRUD operations for managing company profiles within the MetaSoft Letterhead Generator. This system allows users to create, manage, and utilize multiple company profiles for efficient letterhead generation.

### Key Features

- **Multi-Company Support**: Users can manage multiple company profiles
- **Comprehensive Data Management**: Full business information including contact details, branding, and preferences
- **Logo Management**: Upload, store, and manage company logos with optimization
- **Default Company System**: Automatic default company selection for streamlined workflow
- **Letterhead Integration**: Seamless integration with letterhead generation services
- **Activity Tracking**: Monitor usage patterns and company activity
- **Search and Filtering**: Advanced search capabilities across company data

### Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                  Presentation Layer                     │
│              (Livewire Components)                      │
├─────────────────────────────────────────────────────────┤
│                   Service Layer                         │
│                (CompanyService)                         │
├─────────────────────────────────────────────────────────┤
│                   Domain Layer                          │
│               (Company Model)                           │
├─────────────────────────────────────────────────────────┤
│                 Infrastructure                          │
│          (Database, File Storage)                       │
└─────────────────────────────────────────────────────────┘
```

---

## Database Schema

### Companies Table Structure

```sql
CREATE TABLE companies (
    -- Primary Key
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- User Association
    user_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Basic Company Information
    name VARCHAR(255) NOT NULL,
    legal_name VARCHAR(255) NULL,
    trading_name VARCHAR(255) NULL,
    registration_number VARCHAR(100) NULL,
    tax_id VARCHAR(100) NULL,
    
    -- Address Information
    address_line_1 TEXT NOT NULL,
    address_line_2 VARCHAR(255) NULL,
    city VARCHAR(100) NOT NULL,
    state_province VARCHAR(100) NULL,
    postal_code VARCHAR(20) NULL,
    country CHAR(2) NOT NULL DEFAULT 'US',
    
    -- Contact Information
    phone_1 VARCHAR(20) NULL,
    phone_2 VARCHAR(20) NULL,
    fax VARCHAR(20) NULL,
    email_1 VARCHAR(255) NULL,
    email_2 VARCHAR(255) NULL,
    website VARCHAR(255) NULL,
    
    -- Social Media
    linkedin_url VARCHAR(255) NULL,
    twitter_handle VARCHAR(50) NULL,
    facebook_url VARCHAR(255) NULL,
    
    -- Branding
    logo_path VARCHAR(255) NULL,
    logo_alt_text VARCHAR(255) NULL,
    brand_colors JSON NULL,
    primary_color VARCHAR(7) NOT NULL DEFAULT '#000000',
    secondary_color VARCHAR(7) NOT NULL DEFAULT '#666666',
    
    -- Business Information
    industry VARCHAR(100) NULL,
    description TEXT NULL,
    established_year CHAR(4) NULL,
    company_size ENUM('1-10', '11-50', '51-200', '201-500', '501-1000', '1000+') NULL,
    
    -- Letterhead Preferences
    default_template ENUM('classic', 'modern_green', 'corporate_blue', 'elegant_gray') 
        NOT NULL DEFAULT 'classic',
    default_paper_size ENUM('letter', 'a4', 'legal') NOT NULL DEFAULT 'letter',
    include_social_media BOOLEAN NOT NULL DEFAULT FALSE,
    include_registration_details BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Status and Management
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    last_used_at TIMESTAMP NULL,
    
    -- Timestamps
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_user_active (user_id, is_active),
    INDEX idx_user_default (user_id, is_default),
    INDEX idx_name_active (name, is_active),
    INDEX idx_last_used (last_used_at)
);
```

### Field Specifications

#### Required Fields
- `name`: Company display name (max 255 chars)
- `address_line_1`: Primary address line
- `city`: City name
- `country`: ISO 3166-1 alpha-2 country code

#### Optional Business Data
- `legal_name`: Official legal business name
- `trading_name`: DBA or trading name
- `registration_number`: Business registration identifier
- `tax_id`: Tax identification number
- `established_year`: Year company was established (1800-current)
- `company_size`: Employee count categories
- `industry`: Business sector
- `description`: Company description (max 1000 chars)

#### Contact Information
- `phone_1`/`phone_2`: Primary and secondary phone numbers
- `fax`: Fax number
- `email_1`/`email_2`: Primary and secondary email addresses
- `website`: Company website URL

#### Social Media
- `linkedin_url`: LinkedIn company profile
- `twitter_handle`: Twitter handle (without @)
- `facebook_url`: Facebook business page

#### Branding
- `logo_path`: Storage path to company logo
- `logo_alt_text`: Accessibility alt text for logo
- `brand_colors`: JSON array of hex color codes
- `primary_color`/`secondary_color`: Main brand colors (hex format)

---

## Model Architecture

### Company Model (`App\Models\Company`)

#### Key Features

**Eloquent Relationships**
```php
// Belongs to User
public function user(): BelongsTo

// User has many Companies
public function companies(): HasMany
```

**Computed Attributes**
```php
$company->full_address     // Formatted complete address
$company->display_name     // Best display name (trading_name ?? name)
$company->contact_info     // Filtered array of contact details
$company->has_logo        // Boolean: logo exists and accessible
$company->logo_url        // Full URL to logo file
```

**Query Scopes**
```php
Company::active()                    // Only active companies
Company::forUser($userId)            // User's companies
Company::default($userId)            // User's default company
```

**Business Methods**
```php
$company->setAsDefault()             // Set as user's default
$company->toLetterheadData()         // Format for letterhead service
$company->touch()                    // Update last_used_at timestamp
```

#### Model Events

**Automatic Default Management**
- When saving a company as default, automatically removes default status from other user companies
- When retrieving company data on letterhead routes, updates `last_used_at` timestamp

**Data Integrity**
- Ensures only one default company per user
- Cascading deletion when user is deleted
- Automatic logo cleanup on company deletion

---

## Service Layer

### CompanyService (`App\Services\CompanyService`)

The service layer handles all business logic and complex operations for company management.

#### Core Methods

**CRUD Operations**
```php
getUserCompanies(int $userId, array $filters = [], int $perPage = 15)
createCompany(int $userId, array $data, ?UploadedFile $logoFile = null)
updateCompany(Company $company, array $data, ?UploadedFile $logoFile = null)
deleteCompany(Company $company)
```

**Management Operations**
```php
getDefaultCompany(int $userId)
setAsDefault(Company $company)
toggleActiveStatus(Company $company)
duplicateCompany(Company $company, string $newName)
```

**Analytics and Statistics**
```php
getCompanyStatistics(int $userId)
```

#### Validation Rules

**Company Data Validation**
- Name: Required, max 255 characters
- Address: Line 1 required, city required
- Country: Required, 2-character ISO code
- Emails: Valid email format when provided
- URLs: Valid URL format for website and social media
- Colors: Valid hex color format (#RRGGBB or #RGB)
- Year: Between 1800 and current year

**Logo Upload Validation**
- File types: JPEG, PNG, JPG, GIF, SVG
- Maximum size: 2MB
- Must be valid image file

#### File Management

**Logo Upload Process**
1. Validate file type and size
2. Generate unique filename with prefix
3. Store in user-specific directory: `public/logos/user_{userId}/`
4. Return storage path for database

**Logo Management**
- Automatic cleanup on company deletion
- Logo duplication for company cloning
- Storage optimization with organized directory structure

---

## Business Logic

### Default Company Management

**Rules**
1. Each user must have exactly one default company when companies exist
2. First company created automatically becomes default
3. Setting a new default automatically removes previous default
4. Deleting default company automatically promotes another to default
5. Deactivating default company transfers default status

**Implementation**
```php
// Automatic default assignment
if ($existingCompaniesCount === 0) {
    $data['is_default'] = true;
}

// Ensure single default
static::saving(function ($company) {
    if ($company->is_default) {
        static::where('user_id', $company->user_id)
              ->where('id', '!=', $company->id)
              ->update(['is_default' => false]);
    }
});
```

### Activity Tracking

**Last Used Tracking**
- Automatic update when company data accessed on letterhead routes
- Manual update via `touch()` method
- Used for intelligent default selection and analytics

**Usage Statistics**
- Total companies per user
- Active vs inactive breakdown
- Logo usage statistics
- Template preference distribution
- Recent activity metrics (30-day window)

### Data Integrity Rules

**Company Deletion Protection**
- Cannot delete the last active company for a user
- Must have at least one active company
- Automatic default reassignment on deletion

**Status Management**
- Cannot deactivate the last active company
- Deactivating default company triggers new default selection
- Status changes maintain data consistency

---

## Integration Points

### Letterhead Generation Integration

**Data Transformation**
```php
public function toLetterheadData(): array
{
    return [
        'company_name' => $this->display_name,
        'address' => $this->full_address,
        'phone' => $this->phone_1,
        'email' => $this->email_1,
        'website' => $this->website,
        'logo_path' => $this->has_logo ? $this->logo_path : null,
    ];
}
```

**Template Preferences**
- Default template selection from company profile
- Default paper size preference
- Social media inclusion settings
- Registration details inclusion settings

### User Authentication Integration

**User Relationships**
- Companies belong to authenticated users
- Automatic cleanup on user deletion
- User-scoped queries for security

**Permission Model**
- Users can only access their own companies
- All operations scoped by user ID
- Secure file storage with user-based paths

---

## Usage Examples

### Creating a Company

```php
use App\Services\CompanyService;

$companyService = new CompanyService();

$companyData = [
    'name' => 'Acme Corporation',
    'address_line_1' => '123 Business St',
    'city' => 'New York',
    'country' => 'US',
    'phone_1' => '+1-555-0123',
    'email_1' => 'info@acme.com',
    'website' => 'https://acme.com',
    'default_template' => 'corporate_blue',
    'is_default' => true,
];

$logoFile = $request->file('logo'); // UploadedFile instance

$company = $companyService->createCompany(
    $user->id,
    $companyData,
    $logoFile
);
```

### Retrieving Companies

```php
// Get all companies for user with pagination
$companies = $companyService->getUserCompanies($user->id, [], 15);

// Get companies with search filter
$companies = $companyService->getUserCompanies($user->id, [
    'search' => 'acme',
    'active' => true
]);

// Get default company
$defaultCompany = $companyService->getDefaultCompany($user->id);
```

### Updating Company

```php
$updateData = [
    'phone_2' => '+1-555-0124',
    'linkedin_url' => 'https://linkedin.com/company/acme',
    'primary_color' => '#007bff',
];

$newLogoFile = $request->file('logo');

$updatedCompany = $companyService->updateCompany(
    $company,
    $updateData,
    $newLogoFile
);
```

### Integration with Letterhead Generation

```php
use App\Services\LetterheadTemplateService;

// Get user's default company
$company = $companyService->getDefaultCompany($user->id);

// Convert to letterhead data format
$letterheadData = $company->toLetterheadData();

// Use with letterhead service
$letterheadService = new LetterheadTemplateService();
$document = $letterheadService->generateLetterhead(
    $company->default_template,
    $letterheadData,
    $additionalContent
);
```

---

## API Reference

### CompanyService Methods

#### `getUserCompanies(int $userId, array $filters = [], int $perPage = 15)`
Retrieve companies for a user with optional filtering and pagination.

**Parameters:**
- `$userId`: The user ID
- `$filters`: Optional filters array
  - `search`: Search term for name/legal_name/trading_name/industry
  - `active`: Boolean filter for active status
  - `template`: Filter by default template
- `$perPage`: Items per page (0 for all items)

**Returns:** `LengthAwarePaginator|Collection`

#### `createCompany(int $userId, array $data, ?UploadedFile $logoFile = null)`
Create a new company for the specified user.

**Parameters:**
- `$userId`: The user ID
- `$data`: Company data array (see validation rules)
- `$logoFile`: Optional logo file upload

**Returns:** `Company`
**Throws:** `Exception` on validation failure

#### `updateCompany(Company $company, array $data, ?UploadedFile $logoFile = null)`
Update an existing company.

**Parameters:**
- `$company`: Company model instance
- `$data`: Updated data array
- `$logoFile`: Optional new logo file

**Returns:** `Company`
**Throws:** `Exception` on validation failure

#### `deleteCompany(Company $company)`
Delete a company with business logic validation.

**Parameters:**
- `$company`: Company model instance

**Returns:** `bool`
**Throws:** `Exception` if attempting to delete last active company

#### `getCompanyStatistics(int $userId)`
Get comprehensive statistics for user's companies.

**Parameters:**
- `$userId`: The user ID

**Returns:** `array` with keys:
- `total`: Total companies
- `active`: Active companies count
- `inactive`: Inactive companies count
- `with_logo`: Companies with logos
- `templates`: Template usage breakdown
- `recent_activity`: Recently used companies (30 days)

### Company Model Methods

#### `setAsDefault()`
Set this company as the user's default.

**Returns:** `bool`

#### `toLetterheadData()`
Get company data formatted for letterhead generation.

**Returns:** `array` with letterhead-compatible structure

#### Scopes

#### `Company::active()`
Filter for active companies only.

#### `Company::forUser(int $userId)`
Filter for specific user's companies.

#### `Company::default(int $userId)`
Get user's default company.

---

## Security Considerations

### Data Access Control

**User Isolation**
- All queries scoped by user ID
- No cross-user data access possible
- Secure file storage with user-specific paths

**Input Validation**
- Comprehensive validation rules for all fields
- File upload validation (type, size, content)
- SQL injection prevention through Eloquent ORM
- XSS prevention through proper data handling

### File Security

**Logo Upload Security**
- File type validation (whitelist approach)
- File size limits (2MB maximum)
- Unique filename generation prevents conflicts
- User-specific directory structure
- Automatic cleanup on deletion

**Storage Security**
- Files stored in Laravel's secure storage system
- Public access through Laravel's URL generation
- No direct file system access from web

### Privacy and Compliance

**Data Minimization**
- Only collect necessary business information
- Optional fields for sensitive data
- User controls data retention through deletion

**Data Integrity**
- Foreign key constraints maintain referential integrity
- Cascade deletion prevents orphaned data
- Transaction support for complex operations

---

## Performance Optimizations

### Database Optimization

**Indexing Strategy**
```sql
INDEX idx_user_active (user_id, is_active)     -- User company queries
INDEX idx_user_default (user_id, is_default)   -- Default company lookup
INDEX idx_name_active (name, is_active)        -- Company search
INDEX idx_last_used (last_used_at)            -- Activity-based sorting
```

**Query Optimization**
- Scoped queries prevent full table scans
- Eager loading for relationships when needed
- Pagination for large company lists
- Efficient default company resolution

### File Management Optimization

**Storage Strategy**
- User-specific directory organization
- Unique filename generation prevents conflicts
- Automatic cleanup prevents storage bloat
- Efficient duplicate detection for company cloning

### Caching Considerations

**Cacheable Data**
- Company statistics (with time-based invalidation)
- Default company lookup (invalidate on changes)
- Logo URL generation (with file modification checks)

**Cache Keys**
```php
"company_stats_{$userId}"
"default_company_{$userId}"
"company_logo_{$companyId}_{$fileHash}"
```

### Memory Management

**Efficient Loading**
- Use pagination for large datasets
- Load only necessary fields for listings
- Eager load relationships when processing multiple companies
- Use query builders for statistics generation

---

## Error Handling

### Common Exceptions

**Validation Errors**
```php
throw new Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
```

**Business Logic Violations**
```php
throw new Exception('Cannot delete the last active company. Please create another company first.');
throw new Exception('Cannot deactivate the last active company.');
```

**File Operations**
```php
// Handled internally with graceful degradation
// Logo upload failures fall back to text-only letterheads
```

### Error Recovery

**Graceful Degradation**
- Missing logos handled gracefully in letterhead generation
- Company deletion failures preserve data integrity
- File upload failures don't prevent company creation/updates

**Rollback Mechanisms**
- Database transactions for complex operations
- File cleanup on operation failures
- Automatic default company reassignment

---

## Testing Strategy

### Unit Testing

**Model Testing**
- Attribute accessors and mutators
- Relationship definitions
- Scope functionality
- Business method logic

**Service Testing**
- CRUD operations
- Validation logic
- File upload handling
- Business rule enforcement

### Integration Testing

**Database Integration**
- Migration execution
- Foreign key constraints
- Index performance
- Data integrity rules

**File System Integration**
- Logo upload and storage
- File cleanup operations
- Directory structure creation
- Permission handling

### Feature Testing

**Complete Workflows**
- Company creation to letterhead generation
- Default company management
- Multi-company user scenarios
- Error handling and recovery

---

## Deployment Considerations

### Migration Strategy

**Initial Deployment**
```bash
php artisan migrate
```

**Data Migration from Existing System**
```php
// Custom migration for existing company data
// Transform legacy data format to new schema
// Maintain data integrity during transition
```

### Storage Configuration

**File Storage Setup**
```php
// config/filesystems.php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
]
```

**Symbolic Link**
```bash
php artisan storage:link
```

### Performance Monitoring

**Key Metrics**
- Company query response times
- File upload success rates
- Storage usage growth
- Cache hit ratios

**Monitoring Setup**
- Database query logging
- File system monitoring
- Application performance metrics
- Error rate tracking

---

## Future Enhancements

### Planned Features

**Advanced Company Management**
- Company templates for quick setup
- Bulk import/export functionality
- Company sharing between users
- Advanced search with faceted filters

**Enhanced Integration**
- CRM system integration
- Automated company data enrichment
- Business registry API integration
- Advanced analytics and reporting

**Performance Improvements**
- Elasticsearch integration for advanced search
- CDN integration for logo delivery
- Background processing for large operations
- Advanced caching strategies

### Scalability Considerations

**Database Scaling**
- Read replica support
- Sharding strategies for large user bases
- Query optimization monitoring
- Index maintenance automation

**File Storage Scaling**
- Cloud storage integration (S3, etc.)
- CDN implementation
- Image optimization and resizing
- Automated backup strategies

---

This comprehensive documentation provides complete coverage of the Company Management System, from basic usage to advanced deployment considerations. The system is designed to be robust, scalable, and maintainable while providing an excellent user experience for managing multiple company profiles within the letterhead generation workflow.