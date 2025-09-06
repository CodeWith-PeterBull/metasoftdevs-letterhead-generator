#!/bin/bash

##
# Setup Automatic Deployment for Hostinger
# 
# This script sets up automatic deployment by installing a Git post-receive hook
# that runs the Laravel deployment script whenever Hostinger pulls changes.
#
# Usage: Run this script once on your Hostinger server after initial deployment
#        ./scripts/setup-auto-deploy.sh
#
# @author      Metasoftdevs <info@metasoftdevs.com>
# @version     1.0.0
##

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

# Setup function
setup_auto_deploy() {
    log "INFO" "🚀 Setting up automatic deployment for Hostinger..."
    
    # Check if we're in a git repository
    if [ ! -d ".git" ]; then
        log "ERROR" "Not in a git repository. Please run this from your project root."
        exit 1
    fi
    
    # Create hooks directory if it doesn't exist
    if [ ! -d ".git/hooks" ]; then
        mkdir -p ".git/hooks"
        log "INFO" "Created .git/hooks directory"
    fi
    
    # Check if our hook template exists
    if [ ! -f ".git-hooks/post-receive" ]; then
        log "ERROR" "Post-receive hook template not found at .git-hooks/post-receive"
        log "INFO" "Please ensure the repository is up to date"
        exit 1
    fi
    
    # Copy the hook to the correct location
    cp ".git-hooks/post-receive" ".git/hooks/post-receive"
    chmod +x ".git/hooks/post-receive"
    log "SUCCESS" "Post-receive hook installed"
    
    # Make sure deployment script is executable
    if [ -f "scripts/deploy.sh" ]; then
        chmod +x "scripts/deploy.sh"
        log "SUCCESS" "Deployment script is executable"
    else
        log "WARN" "Deployment script not found - it will be available after next git pull"
    fi
    
    # Test the hook installation
    log "INFO" "Testing hook installation..."
    if [ -x ".git/hooks/post-receive" ]; then
        log "SUCCESS" "Post-receive hook is properly installed and executable"
    else
        log "ERROR" "Post-receive hook installation failed"
        exit 1
    fi
    
    log "SUCCESS" "🎉 Automatic deployment setup completed!"
    log "INFO" ""
    log "INFO" "What happens now:"
    log "INFO" "1. When you push code to GitHub → Hostinger Git pulls changes"
    log "INFO" "2. Git post-receive hook triggers → Runs deployment script automatically"
    log "INFO" "3. Your Laravel app is updated → No manual intervention needed"
    log "INFO" ""
    log "INFO" "Deployment logs will be saved to: deployment.log"
    log "INFO" "You can monitor deployments with: tail -f deployment.log"
}

# Backup existing hook if it exists
backup_existing_hook() {
    if [ -f ".git/hooks/post-receive" ]; then
        local backup_name="post-receive.backup.$(date +%Y%m%d_%H%M%S)"
        cp ".git/hooks/post-receive" ".git/hooks/$backup_name"
        log "WARN" "Existing post-receive hook backed up as: $backup_name"
    fi
}

# Main execution
main() {
    log "INFO" "MetaSoft Letterhead Generator - Auto Deploy Setup"
    log "INFO" "=============================================="
    
    # Backup existing hook
    backup_existing_hook
    
    # Setup auto deployment
    setup_auto_deploy
}

# Run main function
main