# Storage Configuration Issues - TODO Fix

## Problem Description

Not using Storage facade causing environment-dependent storage path issues that need to be addressed for proper storage alignment.

## Current Issue

The application uses a custom storage configuration in `config/filesystems.php` with the `USE_NATIVE_STORAGE` environment variable to handle different deployment scenarios:

```php
'public' => [
    'driver'     => 'local',
    'root'       => $useNativeStorage
        ? storage_path('app/public')
        : public_path(),
    'url'        => env('APP_URL') . ($useNativeStorage ? '/storage' : '/public'),
    'visibility' => 'public',
],
```

### Environment Configurations:

-   **Development** (`USE_NATIVE_STORAGE=true`): Files stored in `storage/app/public/` with symlink at `public/storage/`
-   **Production** (`USE_NATIVE_STORAGE=false`): Files stored directly in `public/` directory

## Issues:

**Symlink vs Direct Storage**: Need to ensure proper file access in both scenarios.

## Error Examples

Production environment still shows:

```
Failed to download PDF: url/storage/app/public/invoices/2/invoice_MSDI 1001_1.pdf): Failed to open stream: No such file or directory
```

This suggests the URL generation is still creating paths that expect symlinks when none exist.

## Required Fixes

### 1. Custom Storage Disk Implementation

Create a dedicated disk configuration that properly handles the environment differences:

```php
// In config/filesystems.php
'invoice_storage' => [
    'driver' => 'local',
    'root' => $useNativeStorage
        ? storage_path('app/public')
        : public_path('invoices'),
    'url' => $useNativeStorage
        ? env('APP_URL') . '/storage'
        : env('APP_URL') . '/invoices',
    'visibility' => 'public',
],
```

### 2. Update Invoice Model Methods

Modify PDF storage methods to use the custom disk:

```php
// Use custom disk instead of 'public'
Storage::disk('invoice_storage')->put($path, $pdf->output());
Storage::disk('invoice_storage')->url($this->document_path);
Storage::disk('invoice_storage')->exists($this->document_path);
```

### 3. Environment-Specific URL Handling

Implement proper URL generation that accounts for the storage configuration:

```php
public function getPdfUrl(): ?string
{
    if (!$this->document_path) {
        return null;
    }

    if (config('filesystems.disks.public.root') === public_path()) {
        // Direct public storage
        return url("invoices/{$this->document_path}");
    }

    // Symlink storage
    return Storage::disk('public')->url($this->document_path);
}
```

### 4. Directory Structure Consideration

Ensure the file paths work correctly in both environments:

-   **Development**: `storage/app/public/invoices/user_id/file.pdf` → `domain.com/storage/invoices/user_id/file.pdf`
-   **Production**: `public/invoices/user_id/file.pdf` → `domain.com/invoices/user_id/file.pdf`

## Testing Requirementsss

1. Test PDF generation in both environments
2. Verify URL accessibility in browser
3. Test download and view functionality
4. Ensure proper file permissions

## Priority: HIGH

This affects core invoice functionality and prevents proper PDF access in production environments.

## Files to Modify

-   `config/filesystems.php`
-   `app/Models/Invoice.php`
-   `app/Livewire/InvoiceManagement.php`
-   Environment configuration documentation

## Implementation Notes

-   tests the implementation with temp files where you can import autoloads and simulate request and classes/methods or use tinker for both storage scenarios
-   Document the deployment process for each environment type
-   Ensure backward compatibility during transition

Consult livewire 3 documentation on file storage: https://livewire.laravel.com/docs/uploads#storing-uploaded-files
