# Hyro Package Publishing Script (PowerShell)
# This script automates the process of publishing Hyro to GitHub and Packagist

$ErrorActionPreference = "Stop"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  Hyro Package Publishing Script" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

function Print-Success {
    param($Message)
    Write-Host "✓ $Message" -ForegroundColor Green
}

function Print-Error {
    param($Message)
    Write-Host "✗ $Message" -ForegroundColor Red
}

function Print-Info {
    param($Message)
    Write-Host "ℹ $Message" -ForegroundColor Yellow
}

# Check if we're in the right directory
if (-not (Test-Path "composer.json")) {
    Print-Error "composer.json not found. Please run this script from the package root directory."
    exit 1
}

# Check if package name is correct
$composerJson = Get-Content "composer.json" | ConvertFrom-Json
$packageName = $composerJson.name

if ($packageName -ne "marufsharia/hyro") {
    Print-Error "Package name mismatch. Expected 'marufsharia/hyro', got '$packageName'"
    exit 1
}

Print-Success "Package name verified: $packageName"

# Step 1: Validate composer.json
Write-Host ""
Print-Info "Step 1: Validating composer.json..."
$validateResult = composer validate --strict 2>&1
if ($LASTEXITCODE -eq 0) {
    Print-Success "composer.json is valid"
} else {
    Print-Error "composer.json validation failed"
    Write-Host $validateResult
    exit 1
}

# Step 2: Check for uncommitted changes
Write-Host ""
Print-Info "Step 2: Checking for uncommitted changes..."

if (Test-Path ".git") {
    $status = git status --porcelain
    if ($status) {
        Print-Info "Uncommitted changes found. Committing..."
        git add .
        git commit -m "Prepare for v1.0.0 release"
        Print-Success "Changes committed"
    } else {
        Print-Success "No uncommitted changes"
    }
} else {
    Print-Info "Git repository not initialized. Initializing..."
    git init
    git add .
    git commit -m "Initial commit: Hyro v1.0.0 - Modular Laravel RBAC ecosystem"
    Print-Success "Git repository initialized"
}

# Step 3: Add remote repository
Write-Host ""
Print-Info "Step 3: Configuring remote repository..."
$remoteUrl = "https://github.com/marufsharia/hyro.git"

$remotes = git remote
if ($remotes -contains "origin") {
    $currentUrl = git remote get-url origin
    if ($currentUrl -ne $remoteUrl) {
        Print-Info "Updating remote URL..."
        git remote set-url origin $remoteUrl
        Print-Success "Remote URL updated"
    } else {
        Print-Success "Remote already configured correctly"
    }
} else {
    Print-Info "Adding remote repository..."
    git remote add origin $remoteUrl
    Print-Success "Remote repository added"
}

# Step 4: Create and push main branch
Write-Host ""
Print-Info "Step 4: Pushing to GitHub..."
git branch -M main

try {
    git push -u origin main 2>&1 | Out-Null
    Print-Success "Code pushed to GitHub"
} catch {
    Print-Error "Failed to push to GitHub. Please check your credentials and repository access."
    exit 1
}

# Step 5: Create version tag
Write-Host ""
Print-Info "Step 5: Creating version tag..."
$version = "v1.0.0"

$existingTags = git tag -l
if ($existingTags -contains $version) {
    Print-Info "Tag $version already exists. Skipping..."
} else {
    $tagMessage = @"
Release $version: Initial stable release

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
- 50+ CLI commands
"@
    
    git tag -a $version -m $tagMessage
    Print-Success "Tag $version created"
}

# Step 6: Push tag
Write-Host ""
Print-Info "Step 6: Pushing tag to GitHub..."
try {
    git push origin $version 2>&1 | Out-Null
    Print-Success "Tag pushed to GitHub"
} catch {
    Print-Error "Failed to push tag to GitHub"
    exit 1
}

# Step 7: Display next steps
Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  Publishing Complete!" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Print-Success "Git repository: https://github.com/marufsharia/hyro"
Print-Success "Version tag: $version"
Write-Host ""
Print-Info "Next Steps:"
Write-Host ""
Write-Host "1. Submit to Packagist:"
Write-Host "   → Visit: https://packagist.org/packages/submit"
Write-Host "   → Enter: https://github.com/marufsharia/hyro"
Write-Host "   → Click 'Submit'"
Write-Host ""
Write-Host "2. Wait for Packagist to index (usually 2-5 minutes)"
Write-Host ""
Write-Host "3. Test installation:"
Write-Host "   composer create-project laravel/laravel test-hyro"
Write-Host "   cd test-hyro"
Write-Host "   composer require marufsharia/hyro"
Write-Host ""
Write-Host "4. Verify on Packagist:"
Write-Host "   → https://packagist.org/packages/marufsharia/hyro"
Write-Host ""
Print-Success "All done! Your package is ready for the world! 🎉"
Write-Host ""
