# Contributing to Hyro

Thank you for considering contributing to Hyro! This document outlines the guidelines for contributing to the project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)
- [Testing](#testing)
- [Documentation](#documentation)

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

### Our Standards

- Be respectful and inclusive
- Welcome newcomers and help them learn
- Focus on what is best for the community
- Show empathy towards other community members

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include:

- **Clear title and description**
- **Steps to reproduce** the issue
- **Expected behavior** vs **actual behavior**
- **Environment details** (PHP version, Laravel version, OS)
- **Code samples** or **error messages**

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, include:

- **Clear title and description**
- **Use case** - why is this enhancement needed?
- **Proposed solution** - how should it work?
- **Alternatives considered**

### Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Write or update tests
5. Update documentation
6. Commit your changes (see [Commit Guidelines](#commit-guidelines))
7. Push to your branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

## Development Setup

### Prerequisites

- PHP 8.2 or higher
- Composer 2.0 or higher
- Laravel 12.0 or higher
- Git

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/marufsharia/hyro.git
   cd hyro
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Set up test environment**
   ```bash
   cp .env.example .env.testing
   php artisan key:generate --env=testing
   ```

4. **Run tests**
   ```bash
   composer test
   ```

### Package Structure

Hyro uses a modular architecture with 6 packages:

```
packages/marufsharia/hyro/
├── core/                 # Core authorization system
├── auth/                 # Authentication & 2FA
├── api/                  # RESTful API
├── admin-panel/          # Admin UI
├── crud/                 # CRUD generator
├── plugin/               # Plugin system
├── src/                  # Bridge service provider
├── composer.json
└── README.md
```

## Coding Standards

### PHP Standards

We follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards.

#### Code Style

```php
<?php

namespace Marufsharia\Hyro\Example;

use Illuminate\Support\Facades\DB;
use Marufsharia\Hyro\Models\Role;

class ExampleClass
{
    /**
     * Example method with proper documentation.
     *
     * @param  string  $name
     * @return bool
     */
    public function exampleMethod(string $name): bool
    {
        if (empty($name)) {
            return false;
        }

        return true;
    }
}
```

#### Naming Conventions

- **Classes**: PascalCase (`UserController`, `RoleService`)
- **Methods**: camelCase (`getUserRoles`, `assignPrivilege`)
- **Variables**: camelCase (`$userName`, `$roleList`)
- **Constants**: UPPER_SNAKE_CASE (`MAX_ATTEMPTS`, `DEFAULT_ROLE`)
- **Database tables**: snake_case (`user_roles`, `audit_logs`)

### Laravel Best Practices

- Use Eloquent ORM for database operations
- Use dependency injection
- Follow SOLID principles
- Use Laravel's built-in features when possible
- Write expressive, readable code

### Documentation

- Add PHPDoc blocks for all classes and methods
- Include parameter types and return types
- Provide clear descriptions
- Add `@throws` tags for exceptions

```php
/**
 * Assign a role to the user.
 *
 * @param  \Marufsharia\Hyro\Models\Role|string  $role
 * @param  \Carbon\Carbon|null  $expiresAt
 * @return bool
 * @throws \InvalidArgumentException
 */
public function assignRole($role, ?Carbon $expiresAt = null): bool
{
    // Implementation
}
```

## Commit Guidelines

We follow [Conventional Commits](https://www.conventionalcommits.org/) specification.

### Commit Message Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, missing semicolons, etc.)
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Maintenance tasks
- `ci`: CI/CD changes

### Examples

```bash
feat(crud): add support for image upload in CRUD generator

- Added image field type
- Implemented file upload handling
- Added validation for image files

Closes #123
```

```bash
fix(auth): resolve 2FA verification issue

Fixed an issue where 2FA codes were not being validated correctly
due to timezone differences.

Fixes #456
```

```bash
docs(readme): update installation instructions

Updated the README with clearer installation steps and added
troubleshooting section.
```

## Pull Request Process

### Before Submitting

1. **Update your branch**
   ```bash
   git checkout main
   git pull origin main
   git checkout feature/your-feature
   git rebase main
   ```

2. **Run tests**
   ```bash
   composer test
   ```

3. **Check code style**
   ```bash
   composer lint
   ```

4. **Update documentation**
   - Update README.md if needed
   - Update CHANGELOG.md
   - Add/update PHPDoc blocks

### PR Checklist

- [ ] Code follows PSR-12 standards
- [ ] Tests pass
- [ ] New tests added for new features
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] Commit messages follow conventions
- [ ] No merge conflicts
- [ ] Branch is up to date with main

### PR Template

```markdown
## Description
Brief description of the changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
Describe the tests you ran

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] Tests added/updated
- [ ] No new warnings generated
```

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
./vendor/bin/phpunit tests/Unit/RoleTest.php

# Run with coverage
composer test-coverage
```

### Writing Tests

#### Unit Tests

```php
namespace Marufsharia\Hyro\Tests\Unit;

use Marufsharia\Hyro\Models\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{
    /** @test */
    public function it_can_create_a_role()
    {
        $role = Role::create([
            'name' => 'Editor',
            'slug' => 'editor',
        ]);

        $this->assertDatabaseHas('hyro_roles', [
            'slug' => 'editor',
        ]);
    }
}
```

#### Feature Tests

```php
namespace Marufsharia\Hyro\Tests\Feature;

use Marufsharia\Hyro\Models\User;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/api/hyro/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);
    }
}
```

## Documentation

### Types of Documentation

1. **Code Documentation** - PHPDoc blocks
2. **README.md** - Overview and quick start
3. **DOCUMENTATION.md** - Comprehensive guide
4. **API Documentation** - Endpoint documentation
5. **CHANGELOG.md** - Version history

### Documentation Guidelines

- Write clear, concise documentation
- Include code examples
- Keep documentation up to date
- Use proper markdown formatting
- Add screenshots for UI features

### Example Documentation

```markdown
## Feature Name

Brief description of the feature.

### Usage

\`\`\`php
// Code example
$user->assignRole('admin');
\`\`\`

### Parameters

- `$role` (string|Role) - The role to assign
- `$expiresAt` (Carbon|null) - Optional expiration date

### Returns

Returns `true` on success, `false` on failure.

### Example

\`\`\`php
use Marufsharia\Hyro\Models\Role;

$role = Role::where('slug', 'editor')->first();
$user->assignRole($role);
\`\`\`
```

## Package-Specific Guidelines

### Core Package

- Focus on authorization logic
- Keep dependencies minimal
- Ensure backward compatibility
- Write comprehensive tests

### Auth Package

- Security is paramount
- Follow OWASP guidelines
- Test authentication flows thoroughly
- Document security features

### API Package

- Follow REST conventions
- Validate all inputs
- Return consistent responses
- Document all endpoints

### Admin Panel Package

- Ensure responsive design
- Follow UI/UX best practices
- Test across browsers
- Optimize performance

### CRUD Package

- Generate clean, readable code
- Support all field types
- Test generated code
- Document templates

### Plugin Package

- Ensure plugin isolation
- Validate plugin structure
- Test hot-loading
- Document plugin API

## Getting Help

- **GitHub Issues**: For bugs and feature requests
- **Email**: marufsharia@gmail.com
- **Documentation**: Read DOCUMENTATION.md

## Recognition

Contributors will be recognized in:
- CHANGELOG.md
- GitHub contributors page
- Project README.md

Thank you for contributing to Hyro! 🎉
