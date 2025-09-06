# Simple Deployment Guide - MetaSoft Letterhead Generator

## 🎯 Simplified Deployment Setup

The MetaSoft Letterhead Generator uses a simple, focused deployment approach that works perfectly with Hostinger's Git integration tool.

## 📋 How It Works

### 1. **Hostinger Git Integration**
- Hostinger's Git tool automatically handles code pulling from GitHub
- Composer dependencies are automatically installed
- composer.lock is automatically updated
- No complex CI/CD pipeline needed

### 2. **Simple Laravel Deployment Script**
- **File**: `scripts/deploy.sh`
- **Purpose**: Handle Laravel-specific tasks after code is pulled
- **Tasks**:
  - Clear Laravel caches
  - Install/update dependencies (Composer & NPM)
  - Build production assets
  - Run database migrations
  - Fix file permissions
  - Optimize Laravel for production

---

## 🚀 Deployment Process

### Step 1: Hostinger Setup (One-time)
1. **Enable Git Integration** in Hostinger control panel
2. **Connect Repository**: `git@github.com:Metasoftdevs/letterhead-generator.git`
3. **Set Branch**: `main`
4. **Create Database** in Hostinger MySQL panel
5. **Configure Environment** (.env file)

### Step 2: Setup Automatic Deployment (One-time)
1. **SSH into your Hostinger server**
2. **Navigate to your project directory**
3. **Run the setup script**:
   ```bash
   ./scripts/setup-auto-deploy.sh
   ```

### Step 3: Deploy (Fully Automatic!)
1. **Push code to GitHub** - Hostinger automatically pulls changes
2. **Git hook automatically runs** - Laravel deployment happens automatically
3. **Done!** - Your app is deployed and optimized

That's it! Push and it deploys automatically.

---

## 📝 Deployment Script Details

The `scripts/deploy.sh` script performs these Laravel-specific tasks:

### ⚙️ Environment Setup
```bash
# Copy .env.example to .env if .env doesn't exist
cp .env.example .env
php artisan key:generate --force
```

### 🧹 Cache Management
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 📦 Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build
```

### 🗄️ Database
```bash
php artisan migrate --force
php artisan db:seed --force
```

### 🔐 Permissions & Optimization
```bash
chmod -R 755 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔧 Usage Commands

### Automatic Deployment (Recommended)
```bash
# One-time setup:
./scripts/setup-auto-deploy.sh

# Then just push to GitHub - deployment happens automatically!
git push origin main
```

### Manual Deployment (if needed)
```bash
# SSH into your Hostinger server, then:
cd /home/your-username/public_html
./scripts/deploy.sh
```

### Manual Laravel Tasks
```bash
# Clear caches only
php artisan config:clear && php artisan cache:clear

# Rebuild optimizations
php artisan config:cache && php artisan route:cache

# Run migrations
php artisan migrate --force
```

---

## ✅ Quick Deployment Checklist

### First Time Setup
- [ ] Hostinger Git integration configured
- [ ] Repository connected and pulled
- [ ] Database created in Hostinger
- [ ] .env file configured with database credentials
- [ ] Domain pointed to public_html

### Auto-Deployment Setup
- [ ] Run `./scripts/setup-auto-deploy.sh` once on server
- [ ] Verify Git hook is installed properly

### Each Deployment (Automatic)
- [ ] Push code to GitHub main branch
- [ ] Git hook automatically deploys
- [ ] Check deployment.log for status
- [ ] Test the application

### Verification
- [ ] Site loads correctly
- [ ] Letterhead generation works
- [ ] File uploads function
- [ ] No errors in logs

---

## 🆘 Troubleshooting

### Common Issues

**Script Permission Denied**
```bash
chmod +x scripts/deploy.sh
```

**Database Connection Failed**
- Check .env database credentials
- Verify database exists in Hostinger panel

**File Permissions Error**
```bash
chmod -R 755 storage bootstrap/cache
```

**Assets Not Loading**
```bash
npm run build
php artisan view:cache
```

---

## 📞 Support

- **Developer**: Metasoftdevs <info@metasoftdevs.com>
- **Repository**: https://github.com/Metasoftdevs/letterhead-generator
- **Documentation**: `.docs/hostinger_deployment_docs.md`

---

**🎉 Simple, effective, and reliable deployment for your letterhead generator!**

*Last Updated: January 6, 2025*  
*Version: 1.0.0 - Simplified*