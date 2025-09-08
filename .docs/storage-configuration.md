# Storage Configuration Documentation

## Overview

The MetaSoft Letterhead Generator uses a flexible storage configuration that adapts to different hosting environments, particularly addressing the limitations of shared hosting platforms where symbolic links may not be available.

## Configuration System

### Environment Variable

The storage system is controlled by the `USE_NATIVE_STORAGE` environment variable:

```bash
# On shared hosting (false to turn off storage link requirement)
USE_NATIVE_STORAGE=false

# On any other environment (true for normal server with symlink support)
USE_NATIVE_STORAGE=true
```

### Filesystem Configuration

The configuration automatically adjusts the public disk settings based on the environment:

```php
'public' => [
    'driver'     => 'local',
    'root'       => $useNativeStorage
        // on a "normal" server where you can `php artisan storage:link`
        ? storage_path('app/public')
        // on shared hosting (no symlink)
        : public_path('public'),
    'url'        => env('APP_URL')
        . ($useNativeStorage ? '/storage' : '/public'),
    'visibility' => 'public',
    'throw'      => false,
    'report'     => false,
],
```

## Deployment Scenarios

### Local Development / VPS / Dedicated Server
**Settings:**
```bash
USE_NATIVE_STORAGE=true
```

**How it works:**
- Files stored in `storage/app/public/`
- Accessible via symbolic link: `public/storage -> ../storage/app/public`
- URLs: `https://yourdomain.com/storage/file.jpg`
- Requires: `php artisan storage:link` command

**Setup:**
```bash
php artisan storage:link
```

### Shared Hosting (cPanel, etc.)
**Settings:**
```bash
USE_NATIVE_STORAGE=false
```

**How it works:**
- Files stored directly in `public/public/`
- No symbolic link required
- URLs: `https://yourdomain.com/public/file.jpg`
- Works on hosting platforms that don't support symbolic links

**No additional setup required**

## File Storage Locations

### Company Logos

**Local Development/VPS:**
- Storage: `storage/app/public/logos/user_{user_id}/logo_xxxxx.jpg`
- URL: `https://yourdomain.com/storage/logos/user_1/logo_xxxxx.jpg`

**Shared Hosting:**
- Storage: `public/public/logos/user_{user_id}/logo_xxxxx.jpg`
- URL: `https://yourdomain.com/public/logos/user_1/logo_xxxxx.jpg`

### Temporary Files (Letterhead Generation)

**Local Development/VPS:**
- Storage: `storage/app/public/temp/temp_xxxxx.jpg`
- URL: `https://yourdomain.com/storage/temp/temp_xxxxx.jpg`

**Shared Hosting:**
- Storage: `public/public/temp/temp_xxxxx.jpg`
- URL: `https://yourdomain.com/public/temp/temp_xxxxx.jpg`

## Code Implementation

### Service Layer Usage

All file operations use the explicit `public` disk:

```php
// Upload file
Storage::disk('public')->putFileAs('', $file, $path);

// Check file exists
Storage::disk('public')->exists($path);

// Get file URL
Storage::disk('public')->url($path);

// Delete file
Storage::disk('public')->delete($path);

// Get file path
Storage::disk('public')->path($path);
```

### Model Integration

The Company model automatically handles logo URLs:

```php
// Check if company has logo
$company->has_logo; // Returns boolean

// Get logo URL (adapts to storage configuration)
$company->logo_url; // Returns full URL or null
```

## Migration Guide

### From Default Laravel Storage to Flexible Storage

1. **Update configuration file** (`config/filesystems.php`)
2. **Set environment variable** in `.env` and `.env.example`
3. **Update code** to use explicit disk references
4. **Test both configurations**

### Moving Between Hosting Types

**From Shared Hosting to VPS/Dedicated:**
1. Move files from `public/public/` to `storage/app/public/`
2. Update `USE_NATIVE_STORAGE=true`
3. Run `php artisan storage:link`
4. Update file paths in database if stored

**From VPS/Dedicated to Shared Hosting:**
1. Move files from `storage/app/public/` to `public/public/`
2. Update `USE_NATIVE_STORAGE=false`
3. Remove symbolic link if exists
4. Update file paths in database if stored

## Security Considerations

### File Access Control

**Local Development/VPS:**
- Files served through Laravel's storage system
- Better control over access permissions
- Can implement authentication middleware

**Shared Hosting:**
- Files directly accessible via web server
- Rely on web server configuration for security
- Consider .htaccess rules for sensitive files

### File Validation

Both configurations use the same validation rules:

```php
const LOGO_VALIDATION_RULES = [
    'logo' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
];
```

## Performance Considerations

### Local Development/VPS
- **Pros:** Better security, Laravel integration
- **Cons:** Additional symlink dependency

### Shared Hosting
- **Pros:** Direct file access, no symlink required
- **Cons:** Files directly accessible, limited access control

## Troubleshooting

### Common Issues

**"Storage link not found" error:**
- Check if symbolic link exists: `ls -la public/storage`
- Run: `php artisan storage:link`
- Verify web server permissions

**Files not accessible on shared hosting:**
- Verify `USE_NATIVE_STORAGE=false`
- Check `public/public/` directory exists
- Verify web server can access public directory

**Logo URLs not working:**
- Check `APP_URL` in `.env` file
- Verify file permissions (644 for files, 755 for directories)
- Test file access directly via URL

### Debug Commands

```bash
# Check storage configuration
php artisan storage:link

# Clear configuration cache
php artisan config:clear

# Check file permissions
ls -la public/
ls -la storage/app/public/

# Test file upload
php artisan tinker
Storage::disk('public')->put('test.txt', 'test content');
Storage::disk('public')->url('test.txt');
```

## Environment Configuration Examples

### Local Development (.env)
```bash
APP_NAME="MetaSoft Letterheads"
APP_ENV=local
APP_URL=http://localhost:8000
USE_NATIVE_STORAGE=true
```

### Production VPS (.env)
```bash
APP_NAME="MetaSoft Letterheads"
APP_ENV=production
APP_URL=https://yourdomain.com
USE_NATIVE_STORAGE=true
```

### Shared Hosting (.env)
```bash
APP_NAME="MetaSoft Letterheads"
APP_ENV=production
APP_URL=https://yourdomain.com
USE_NATIVE_STORAGE=false
```

## Best Practices

### Development
1. Always test both storage configurations
2. Use explicit disk references in code
3. Implement proper file validation
4. Handle missing files gracefully

### Deployment
1. Set correct `USE_NATIVE_STORAGE` value before deployment
2. Verify file permissions after deployment
3. Test file upload/access functionality
4. Monitor storage usage and cleanup temporary files

### Maintenance
1. Regular cleanup of temporary files
2. Monitor storage usage
3. Backup logo files during system updates
4. Document hosting-specific configuration

This flexible storage system ensures the MetaSoft Letterhead Generator works reliably across different hosting environments while maintaining security and performance standards.