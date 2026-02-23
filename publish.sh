#!/bin/bash

# Hyro Package Publishing Script
# This script automates the process of publishing Hyro to GitHub and Packagist

set -e  # Exit on error

echo "=========================================="
echo "  Hyro Package Publishing Script"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    print_error "composer.json not found. Please run this script from the package root directory."
    exit 1
fi

# Check if package name is correct
PACKAGE_NAME=$(grep -o '"name": *"[^"]*"' composer.json | head -1 | cut -d'"' -f4)
if [ "$PACKAGE_NAME" != "marufsharia/hyro" ]; then
    print_error "Package name mismatch. Expected 'marufsharia/hyro', got '$PACKAGE_NAME'"
    exit 1
fi

print_success "Package name verified: $PACKAGE_NAME"

# Step 1: Validate composer.json
echo ""
print_info "Step 1: Validating composer.json..."
if composer validate --strict; then
    print_success "composer.json is valid"
else
    print_error "composer.json validation failed"
    exit 1
fi

# Step 2: Check for uncommitted changes
echo ""
print_info "Step 2: Checking for uncommitted changes..."
if [ -d ".git" ]; then
    if [ -n "$(git status --porcelain)" ]; then
        print_info "Uncommitted changes found. Committing..."
        git add .
        git commit -m "Prepare for v1.0.0 release"
        print_success "Changes committed"
    else
        print_success "No uncommitted changes"
    fi
else
    print_info "Git repository not initialized. Initializing..."
    git init
    git add .
    git commit -m "Initial commit: Hyro v1.0.0 - Modular Laravel RBAC ecosystem"
    print_success "Git repository initialized"
fi

# Step 3: Add remote repository
echo ""
print_info "Step 3: Configuring remote repository..."
REMOTE_URL="https://github.com/marufsharia/hyro.git"

if git remote | grep -q "origin"; then
    CURRENT_URL=$(git remote get-url origin)
    if [ "$CURRENT_URL" != "$REMOTE_URL" ]; then
        print_info "Updating remote URL..."
        git remote set-url origin "$REMOTE_URL"
        print_success "Remote URL updated"
    else
        print_success "Remote already configured correctly"
    fi
else
    print_info "Adding remote repository..."
    git remote add origin "$REMOTE_URL"
    print_success "Remote repository added"
fi

# Step 4: Create and push main branch
echo ""
print_info "Step 4: Pushing to GitHub..."
git branch -M main

if git push -u origin main; then
    print_success "Code pushed to GitHub"
else
    print_error "Failed to push to GitHub. Please check your credentials and repository access."
    exit 1
fi

# Step 5: Create version tag
echo ""
print_info "Step 5: Creating version tag..."
VERSION="v1.0.0"

if git tag -l | grep -q "^$VERSION$"; then
    print_info "Tag $VERSION already exists. Skipping..."
else
    git tag -a "$VERSION" -m "Release $VERSION: Initial stable release

Features:
- Modular architecture with 6 independent packages
- Advanced RBAC with wildcard privileges
- Beautiful admin panel with Livewire 3
- Powerful CRUD generator with 12+ templates
- RESTful API with Sanctum authentication
- Plugin system with hot-loading
- Two-factor authentication (2FA)
- Comprehensive audit logging
- User profile management
- 50+ CLI commands"
    
    print_success "Tag $VERSION created"
fi

# Step 6: Push tag
echo ""
print_info "Step 6: Pushing tag to GitHub..."
if git push origin "$VERSION"; then
    print_success "Tag pushed to GitHub"
else
    print_error "Failed to push tag to GitHub"
    exit 1
fi

# Step 7: Display next steps
echo ""
echo "=========================================="
echo "  Publishing Complete!"
echo "=========================================="
echo ""
print_success "Git repository: https://github.com/marufsharia/hyro"
print_success "Version tag: $VERSION"
echo ""
print_info "Next Steps:"
echo ""
echo "1. Submit to Packagist:"
echo "   → Visit: https://packagist.org/packages/submit"
echo "   → Enter: https://github.com/marufsharia/hyro"
echo "   → Click 'Submit'"
echo ""
echo "2. Wait for Packagist to index (usually 2-5 minutes)"
echo ""
echo "3. Test installation:"
echo "   composer create-project laravel/laravel test-hyro"
echo "   cd test-hyro"
echo "   composer require marufsharia/hyro"
echo ""
echo "4. Verify on Packagist:"
echo "   → https://packagist.org/packages/marufsharia/hyro"
echo ""
print_success "All done! Your package is ready for the world! 🎉"
echo ""
