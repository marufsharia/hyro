# 🤝 Contributing to Hyro

Thank you for considering contributing to Hyro! This document provides guidelines for contributing to the project.

---

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)

---

## 📜 Code of Conduct

### Our Pledge

We pledge to make participation in our project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, personal appearance, race, religion, or sexual identity and orientation.

### Our Standards

**Positive behavior includes:**
- Using welcoming and inclusive language
- Being respectful of differing viewpoints
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards other community members

**Unacceptable behavior includes:**
- Trolling, insulting/derogatory comments, and personal attacks
- Public or private harassment
- Publishing others' private information without permission
- Other conduct which could reasonably be considered inappropriate

---

## 🚀 Getting Started

### Prerequisites

- PHP 8.2+
- Composer 2.0+
- Laravel 12+
- Git
- Node.js 18+
- MySQL/PostgreSQL/SQLite

### Fork and Clone

```bash
# Fork the repository on GitHub
# Then clone your fork
git clone https://github.com/YOUR-USERNAME/hyro.git
cd hyro

# Add upstream remote
git remote add upstream https://github.com/marufsharia/hyro.git
```

---

## 💻 Development Setup

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Set Up Test Environment

```bash
# Copy test environment file
cp .env.testing.example .env.testing

# Create test database
php artisan migrate --env=testing
```

### 3. Run Tests

```bash
# Run all tests
composer test

# Run specific test suite
composer test:unit
composer test:feature

# Run with coverage
composer test:coverage
```

### 4. Code Quality Checks

```bash
# Run PHP CS Fixer
composer format

# Run PHPStan
composer analyze

# Run all checks
composer check
```

---

## 📝 Coding Standards

### PHP Standards

We follow **PSR-12** coding standards.

#### Class Structure

```php
<?php

namespace Marufsharia\Hyro\Services;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service class description.
 */
class ExampleService
{
    /**
     * Method description.
     *
     * @param string $param Parameter description
     * @return array Result description
     * @throws Exception When something goes wrong
     */
    public function exampleMethod(string $param): array
    {
        // Implementation
    }
}
```

#### Naming Conventions

- **Classes:** PascalCase (`UserService`, `RoleRepository`)
- **Methods:** camelCase (`getUserRoles`, `assignPrivilege`)
- **Variables:** camelCase (`$userName`, `$roleList`)
- **Constants:** UPPER_SNAKE_CASE (`MAX_ATTEMPTS`, `DEFAULT_TTL`)

#### Documentation

All public methods must have PHPDoc blocks:

```php
/**
 * Assign a role to a user.
 *
 * @param User $user The user to assign the role to
 * @param string $role The role slug
 * @param \DateTime|null $expiresAt Optional expiration date
 * @return bool True if successful
 * @throws RoleNotFoundException If role doesn't exist
 */
public function assignRole(User $user, string $role, ?\DateTime $expiresAt = null): bool
{
    // Implementation
}
```

### JavaScript Standards

We follow **ESLint** standards.

```javascript
// Use const/let, not var
const userName = 'John';
let counter = 0;

// Use arrow functions
const greet = (name) => {
    return `Hello, ${name}!`;
};

// Use template literals
console.log(`User: ${userName}`);
```

### Blade Templates

```blade
{{-- Use proper indentation --}}
<div class="container">
    @if($user->hasRole('admin'))
        <h1>Admin Panel</h1>
    @endif
</div>

{{-- Use components --}}
<x-hyro-protected privilege="users.view">
    <p>Protected content</p>
</x-hyro-protected>
```

---

## 🔄 Pull Request Process

### 1. Create a Branch

```bash
# Update your fork
git fetch upstream
git checkout main
git merge upstream/main

# Create feature branch
git checkout -b feature/your-feature-name

# Or bug fix branch
git checkout -b fix/bug-description
```

### 2. Make Changes

- Write clean, readable code
- Follow coding standards
- Add tests for new features
- Update documentation
- Keep commits atomic and focused

### 3. Commit Changes

Use conventional commit messages:

```bash
# Feature
git commit -m "feat: add user suspension feature"

# Bug fix
git commit -m "fix: resolve role assignment issue"

# Documentation
git commit -m "docs: update installation guide"

# Refactor
git commit -m "refactor: improve authorization service"

# Test
git commit -m "test: add tests for privilege system"
```

### 4. Push and Create PR

```bash
# Push to your fork
git push origin feature/your-feature-name

# Create pull request on GitHub
```

### 5. PR Requirements

Your PR must:
- [ ] Pass all tests
- [ ] Follow coding standards
- [ ] Include tests for new features
- [ ] Update relevant documentation
- [ ] Have a clear description
- [ ] Reference related issues

### 6. PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests added/updated
- [ ] Feature tests added/updated
- [ ] Manual testing performed

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] No new warnings generated
- [ ] Tests pass locally
```

---

## 🐛 Reporting Bugs

### Before Reporting

1. Check existing issues
2. Verify it's reproducible
3. Test on latest version
4. Gather relevant information

### Bug Report Template

```markdown
**Describe the bug**
A clear description of the bug.

**To Reproduce**
Steps to reproduce:
1. Go to '...'
2. Click on '...'
3. See error

**Expected behavior**
What you expected to happen.

**Actual behavior**
What actually happened.

**Environment:**
- PHP version:
- Laravel version:
- Hyro version:
- Database:
- OS:

**Additional context**
Any other relevant information.

**Stack trace**
```
Error stack trace here
```
```

---

## 💡 Suggesting Features

### Feature Request Template

```markdown
**Is your feature request related to a problem?**
A clear description of the problem.

**Describe the solution you'd like**
A clear description of what you want to happen.

**Describe alternatives you've considered**
Alternative solutions or features you've considered.

**Additional context**
Any other context or screenshots.

**Would you like to implement this feature?**
- [ ] Yes, I can implement this
- [ ] No, I need help
```

---

## 🧪 Testing Guidelines

### Writing Tests

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Marufsharia\Hyro\Services\AuthorizationService;

class AuthorizationServiceTest extends TestCase
{
    /** @test */
    public function it_can_check_user_privilege()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $service = new AuthorizationService();
        
        $this->assertTrue($service->hasPrivilege($user, 'users.view'));
    }
}
```

### Test Coverage

- Aim for 80%+ code coverage
- Test happy paths and edge cases
- Test error conditions
- Mock external dependencies

---

## 📚 Documentation Guidelines

### Code Comments

```php
// Good: Explains why, not what
// Cache the result to avoid repeated database queries
$roles = Cache::remember('user_roles_' . $user->id, 3600, function () use ($user) {
    return $user->roles;
});

// Bad: States the obvious
// Get the user's roles
$roles = $user->roles;
```

### Documentation Files

- Use Markdown format
- Include code examples
- Keep it up to date
- Add table of contents for long docs

---

## 🏆 Recognition

Contributors will be:
- Listed in CONTRIBUTORS.md
- Mentioned in release notes
- Credited in documentation

---

## 📞 Getting Help

- **GitHub Issues:** For bugs and features
- **Discussions:** For questions and ideas
- **Email:** marufsharia@gmail.com

---

## 📄 License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

**Thank you for contributing to Hyro!** 🎉
