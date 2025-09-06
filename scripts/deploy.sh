#!/bin/bash

##
# Simple Laravel Deployment Script for Hostinger
# 
# This script handles Laravel-specific deployment tasks after Hostinger's Git tool
# has already pulled the latest code from the repository.
#
# Tasks:
# - Clear Laravel caches
# - Install/update Composer dependencies
# - Install/update NPM dependencies
# - Run database migrations and seeding
# - Fix file permissions
# - Optimize Laravel for production
#
# Usage: ./scripts/deploy.sh
#
# @author      Metasoftdevs <info@metasoftdevs.com>
# @version     1.0.0
##

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Simple logging
log() {
    local level=$1
    local message=$2
    
    case $level in
        "INFO")  echo -e "${BLUE}[INFO]${NC} $message" ;;
        "WARN")  echo -e "${YELLOW}[WARN]${NC} $message" ;;
        "ERROR") echo -e "${RED}[ERROR]${NC} $message" ;;
        "SUCCESS") echo -e "${GREEN}[SUCCESS]${NC} $message" ;;
    esac
}

# Main deployment function
deploy() {
    log "INFO" "🚀 Starting Laravel deployment process..."
    
    # Step 1: Clear Laravel caches
    log "INFO" "🧹 Clearing Laravel caches..."
    php artisan config:clear || log "WARN" "Config clear failed (might not exist)"
    php artisan cache:clear || log "WARN" "Cache clear failed (might not exist)" 
    php artisan view:clear || log "WARN" "View clear failed (might not exist)"
    php artisan route:clear || log "WARN" "Route clear failed (might not exist)"
    log "SUCCESS" "Caches cleared"
    
    # Step 2: Install Composer dependencies
    log "INFO" "📦 Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
    if [ $? -eq 0 ]; then
        log "SUCCESS" "Composer dependencies installed"
    else
        log "ERROR" "Composer install failed"
        exit 1
    fi
    
    # Step 3: Install NPM dependencies and build assets
    log "INFO" "📦 Installing NPM dependencies..."
    npm ci --production
    if [ $? -eq 0 ]; then
        log "SUCCESS" "NPM dependencies installed"
    else
        log "WARN" "NPM install failed, continuing anyway"
    fi
    
    log "INFO" "🎨 Building production assets..."
    npm run build
    if [ $? -eq 0 ]; then
        log "SUCCESS" "Assets built successfully"
    else
        log "WARN" "Asset build failed, continuing anyway"
    fi
    
    # Step 4: Generate application key if needed
    if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
        log "INFO" "🔑 Generating application key..."
        php artisan key:generate --force
        log "SUCCESS" "Application key generated"
    else
        log "INFO" "Application key already exists"
    fi
    
    # Step 5: Run database migrations
    log "INFO" "🗄️ Running database migrations..."
    php artisan migrate --force
    if [ $? -eq 0 ]; then
        log "SUCCESS" "Database migrations completed"
    else
        log "WARN" "Database migrations failed (might be expected)"
    fi
    
    # Step 6: Seed database (only if needed)
    log "INFO" "🌱 Seeding database..."
    php artisan db:seed --force --class=DatabaseSeeder
    if [ $? -eq 0 ]; then
        log "SUCCESS" "Database seeding completed"
    else
        log "WARN" "Database seeding failed or skipped"
    fi
    
    # Step 7: Fix file permissions
    log "INFO" "🔐 Setting file permissions..."
    chmod -R 755 storage bootstrap/cache 2>/dev/null || log "WARN" "Permission setting failed"
    find storage -type f -exec chmod 644 {} \; 2>/dev/null || log "WARN" "Storage file permissions failed"
    find bootstrap/cache -type f -exec chmod 644 {} \; 2>/dev/null || log "WARN" "Cache file permissions failed"
    log "SUCCESS" "File permissions set"
    
    # Step 8: Optimize Laravel for production
    log "INFO" "⚡ Optimizing Laravel for production..."
    php artisan config:cache
    php artisan route:cache  
    php artisan view:cache
    
    if [ $? -eq 0 ]; then
        log "SUCCESS" "Laravel optimization completed"
    else
        log "WARN" "Laravel optimization had issues"
    fi
    
    # Step 9: Final health check
    log "INFO" "🏥 Running health check..."
    if php artisan about --only=environment >/dev/null 2>&1; then
        log "SUCCESS" "Health check passed"
    else
        log "WARN" "Health check failed but deployment continued"
    fi
    
    log "SUCCESS" "🎉 Laravel deployment completed successfully!"
    log "INFO" "Your application is ready at: $(grep APP_URL .env 2>/dev/null | cut -d'=' -f2 || echo 'your-domain.com')"
}

# Run deployment
deploy