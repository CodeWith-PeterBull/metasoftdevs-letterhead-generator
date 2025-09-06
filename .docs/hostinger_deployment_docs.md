# Hostinger Deployment Guide - MetaSoft Letterhead Generator

## Table of Contents
1. [Pre-deployment Requirements](#pre-deployment-requirements)
2. [Hostinger Configuration](#hostinger-configuration)
3. [Git Integration Setup](#git-integration-setup)
4. [Environment Configuration](#environment-configuration)
5. [Database Setup](#database-setup)
6. [CI/CD Pipeline Configuration](#cicd-pipeline-configuration)
7. [SSL Certificate Setup](#ssl-certificate-setup)
8. [Performance Optimization](#performance-optimization)
9. [Monitoring and Logging](#monitoring-and-logging)
10. [Troubleshooting Guide](#troubleshooting-guide)

---

## Pre-deployment Requirements

### 1. Hostinger Account Setup
- **Hosting Plan**: Business or Premium plan (required for Git integration)
- **Domain**: Configure custom domain or use provided subdomain
- **PHP Version**: PHP 8.1 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+

### 2. Required Hostinger Features
- ✅ Git Integration Tool
- ✅ SSH Access (for advanced configurations)
- ✅ SSL Certificate (free Let's Encrypt)
- ✅ Cron Jobs (for Laravel scheduler)
- ✅ Node.js support (for asset compilation)

---

## Hostinger Configuration

### Step 1: Access Hostinger Control Panel

1. **Login to Hostinger**
   ```
   URL: https://hpanel.hostinger.com
   Account: Metasoftdevs credentials
   ```

2. **Navigate to Website Management**
   - Select your domain/website
   - Go to "Advanced" → "Git"

### Step 2: Enable Git Integration

1. **Access Git Tool**
   ```
   Hostinger Panel → Advanced → Git → Create Repository
   ```

2. **Repository Configuration**
   ```
   Repository URL: git@github.com:Metasoftdevs/letterhead-generator.git
   Branch: main
   Target Directory: public_html (or custom directory)
   ```

3. **SSH Key Setup**
   ```bash
   # Generate SSH key in Hostinger (if not done)
   ssh-keygen -t rsa -b 4096 -C "info@metasoftdevs.com"
   
   # Add public key to GitHub repository
   # Deploy keys: Repository → Settings → Deploy keys
   ```

---

## Git Integration Setup

### Step 3: Configure Deployment Directory

1. **Directory Structure**
   ```
   /public_html/
   ├── letterhead-app/          # Laravel application root
   │   ├── app/
   │   ├── config/
   │   ├── database/
   │   ├── public/              # Symlink to public_html root
   │   └── ...
   └── index.php                # Symlinked from letterhead-app/public/
   ```

2. **Create Deployment Script**
   ```bash
   # /public_html/deploy.sh
   #!/bin/bash
   cd /home/[username]/public_html/letterhead-app
   
   # Pull latest changes
   git pull origin main
   
   # Install/Update dependencies
   composer install --no-dev --optimize-autoloader
   npm ci --production
   
   # Clear and rebuild caches
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   
   # Run optimizations
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   
   # Database migrations (if needed)
   php artisan migrate --force
   
   # Build frontend assets
   npm run build
   ```

---

## Environment Configuration

### Step 4: Production Environment Setup

1. **Create Production .env File**
   ```env
   APP_NAME="MetaSoft Letterhead Generator"
   APP_ENV=production
   APP_KEY=base64:GENERATED_KEY_HERE
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   
   LOG_CHANNEL=single
   LOG_LEVEL=error
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=hostinger_database_name
   DB_USERNAME=hostinger_db_user
   DB_PASSWORD=secure_password_here
   
   BROADCAST_DRIVER=log
   CACHE_DRIVER=file
   FILESYSTEM_DISK=local
   QUEUE_CONNECTION=sync
   SESSION_DRIVER=file
   SESSION_LIFETIME=120
   
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.hostinger.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@domain.com
   MAIL_PASSWORD=email_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="info@metasoftdevs.com"
   MAIL_FROM_NAME="MetaSoft Developers"
   
   # Security Settings
   SESSION_SECURE_COOKIE=true
   SANCTUM_STATEFUL_DOMAINS=your-domain.com
   ```

2. **Security Configurations**
   ```bash
   # Set proper file permissions
   find /path/to/project -type f -exec chmod 644 {} \;
   find /path/to/project -type d -exec chmod 755 {} \;
   chmod -R 777 storage/
   chmod -R 777 bootstrap/cache/
   ```

---

## Database Setup

### Step 5: Database Configuration

1. **Create Database in Hostinger**
   ```
   Hostinger Panel → Databases → MySQL Databases
   - Create new database
   - Create database user
   - Assign user to database with all privileges
   ```

2. **Database Migration Script**
   ```sql
   -- Initial database setup
   CREATE DATABASE letterhead_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   
   -- User setup (if creating manually)
   CREATE USER 'letterhead_user'@'localhost' IDENTIFIED BY 'secure_password';
   GRANT ALL PRIVILEGES ON letterhead_prod.* TO 'letterhead_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. **Migration Commands**
   ```bash
   # Run in production
   php artisan migrate --force
   php artisan db:seed --force --class=ProductionSeeder
   ```

---

## CI/CD Pipeline Configuration

### Step 6: GitHub Actions Workflow

Create `.github/workflows/hostinger-deploy.yml`:

```yaml
name: Deploy to Hostinger

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql, dom, filter, gd, json
        
    - name: Copy environment file
      run: cp .env.example .env.testing
      
    - name: Install Dependencies
      run: composer install --prefer-dist --no-progress --no-suggest
      
    - name: Generate application key
      run: php artisan key:generate --env=testing
      
    - name: Run database migrations
      run: php artisan migrate --env=testing --force
      
    - name: Install NPM dependencies
      run: npm ci
      
    - name: Build assets
      run: npm run build
      
    - name: Run Tests
      run: php artisan test
      
    - name: Run PHPStan (Static Analysis)
      run: ./vendor/bin/phpstan analyse --memory-limit=2G

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        
    - name: Install Dependencies
      run: |
        composer install --no-dev --optimize-autoloader
        npm ci --production
        npm run build
        
    - name: Create deployment artifact
      run: |
        tar -czf app.tar.gz \
          --exclude='.git' \
          --exclude='node_modules' \
          --exclude='tests' \
          --exclude='storage/logs/*' \
          --exclude='storage/framework/cache/*' \
          --exclude='storage/framework/sessions/*' \
          --exclude='storage/framework/views/*' \
          .
          
    - name: Deploy to Hostinger
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.HOSTINGER_HOST }}
        username: ${{ secrets.HOSTINGER_USERNAME }}
        password: ${{ secrets.HOSTINGER_PASSWORD }}
        port: 22
        script: |
          cd /home/${{ secrets.HOSTINGER_USERNAME }}/public_html
          
          # Backup current deployment
          if [ -d "letterhead-app" ]; then
            mv letterhead-app letterhead-app-backup-$(date +%Y%m%d_%H%M%S)
          fi
          
          # Create new deployment directory
          mkdir -p letterhead-app-new
          cd letterhead-app-new
          
          # Pull latest code
          git clone --depth 1 --branch main git@github.com:Metasoftdevs/letterhead-generator.git .
          
          # Install dependencies
          composer install --no-dev --optimize-autoloader
          npm ci --production
          npm run build
          
          # Set up environment
          cp .env.production .env
          php artisan key:generate --force
          
          # Cache optimization
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          
          # Database operations
          php artisan migrate --force
          
          # Set permissions
          chmod -R 755 storage bootstrap/cache
          
          # Atomic deployment switch
          cd /home/${{ secrets.HOSTINGER_USERNAME }}/public_html
          mv letterhead-app-new letterhead-app
          
          # Update symlinks if needed
          ln -sf /home/${{ secrets.HOSTINGER_USERNAME }}/public_html/letterhead-app/public/* .
          
          # Cleanup old backups (keep last 3)
          ls -td letterhead-app-backup-* | tail -n +4 | xargs rm -rf
```

### Step 7: GitHub Secrets Configuration

Add these secrets in GitHub Repository → Settings → Secrets:

```
HOSTINGER_HOST=your-hostinger-server.com
HOSTINGER_USERNAME=your_hostinger_username  
HOSTINGER_PASSWORD=your_hostinger_password
HOSTINGER_DB_PASSWORD=your_database_password
```

---

## SSL Certificate Setup

### Step 8: Enable HTTPS

1. **Let's Encrypt SSL (Free)**
   ```
   Hostinger Panel → SSL → Manage → Enable Let's Encrypt
   ```

2. **Force HTTPS Redirect**
   ```apache
   # Add to .htaccess in public_html
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

3. **Laravel HTTPS Configuration**
   ```php
   // Add to AppServiceProvider boot() method
   if (config('app.env') === 'production') {
       \URL::forceScheme('https');
   }
   ```

---

## Performance Optimization

### Step 9: Production Optimizations

1. **OPcache Configuration** (php.ini)
   ```ini
   opcache.enable=1
   opcache.memory_consumption=256
   opcache.max_accelerated_files=20000
   opcache.validate_timestamps=0
   opcache.save_comments=1
   opcache.fast_shutdown=0
   ```

2. **Laravel Optimizations**
   ```bash
   # Production optimization commands
   php artisan config:cache
   php artisan route:cache  
   php artisan view:cache
   php artisan event:cache
   composer install --optimize-autoloader --no-dev
   ```

3. **Asset Optimization**
   ```javascript
   // vite.config.js production settings
   export default defineConfig({
     plugins: [laravel({
       input: ['resources/css/app.css', 'resources/js/app.js'],
       refresh: true,
     })],
     build: {
       minify: 'terser',
       rollupOptions: {
         output: {
           manualChunks: {
             vendor: ['lodash', 'axios'],
           }
         }
       }
     }
   });
   ```

---

## Monitoring and Logging

### Step 10: Production Monitoring

1. **Laravel Logging Configuration**
   ```php
   // config/logging.php
   'channels' => [
       'production' => [
           'driver' => 'daily',
           'path' => storage_path('logs/laravel.log'),
           'level' => 'error',
           'days' => 30,
       ],
   ],
   ```

2. **Error Tracking Setup**
   ```bash
   # Install error tracking (optional)
   composer require sentry/sentry-laravel
   php artisan sentry:publish --dsn=YOUR_SENTRY_DSN
   ```

3. **Health Check Endpoint**
   ```php
   // routes/web.php
   Route::get('/health', function () {
       return response()->json([
           'status' => 'healthy',
           'timestamp' => now(),
           'environment' => app()->environment(),
           'version' => '1.0.0'
       ]);
   })->name('health.check');
   ```

---

## Cron Jobs Setup

### Step 11: Laravel Scheduler

1. **Hostinger Cron Job Configuration**
   ```
   Hostinger Panel → Advanced → Cron Jobs
   Command: /usr/bin/php /home/username/public_html/letterhead-app/artisan schedule:run
   Frequency: Every minute (* * * * *)
   ```

2. **Laravel Scheduler Tasks** (if needed)
   ```php
   // app/Console/Kernel.php
   protected function schedule(Schedule $schedule)
   {
       // Cleanup temporary files
       $schedule->command('letterhead:cleanup')->daily();
       
       // Generate usage reports
       $schedule->command('letterhead:reports')->weekly();
   }
   ```

---

## Troubleshooting Guide

### Common Issues and Solutions

1. **Permission Errors**
   ```bash
   # Fix storage permissions
   chmod -R 775 storage/
   chmod -R 775 bootstrap/cache/
   chown -R www-data:www-data storage/
   ```

2. **Database Connection Issues**
   ```bash
   # Test database connection
   php artisan tinker
   DB::connection()->getPdo();
   ```

3. **Asset Loading Problems**
   ```bash
   # Rebuild assets
   npm run build
   php artisan view:clear
   ```

4. **Environment Issues**
   ```bash
   # Clear all caches
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

### Emergency Rollback Procedure

```bash
# Quick rollback to previous version
cd /home/username/public_html
mv letterhead-app letterhead-app-failed
mv letterhead-app-backup-[timestamp] letterhead-app
```

---

## Security Checklist

- [ ] SSL certificate installed and configured
- [ ] Database credentials secured
- [ ] File permissions properly set (644/755)
- [ ] Debug mode disabled in production
- [ ] Error reporting configured appropriately  
- [ ] Session security configured
- [ ] CSRF protection enabled
- [ ] Input validation implemented
- [ ] File upload security measures in place

---

## Post-Deployment Verification

1. **Functional Tests**
   - [ ] Home page loads correctly
   - [ ] User authentication works
   - [ ] Letterhead generation (PDF/Word) functional
   - [ ] File uploads work properly
   - [ ] Email notifications sent successfully

2. **Performance Tests**
   - [ ] Page load times < 3 seconds
   - [ ] Database queries optimized
   - [ ] Assets compressed and cached
   - [ ] Memory usage within limits

3. **Security Tests**
   - [ ] HTTPS redirect working
   - [ ] XSS protection enabled
   - [ ] SQL injection prevention verified
   - [ ] File upload restrictions enforced

---

## Maintenance Schedule

### Daily
- Monitor error logs
- Check disk space usage
- Verify backup integrity

### Weekly  
- Review performance metrics
- Update security patches
- Clean temporary files

### Monthly
- Full security audit
- Database optimization
- Performance tuning review

---

**Deployment Contact Information:**
- **Technical Lead**: Metasoftdevs <info@metasoftdevs.com>
- **Website**: https://www.metasoftdevs.com
- **Repository**: https://github.com/Metasoftdevs/letterhead-generator

*Last Updated: January 2025*