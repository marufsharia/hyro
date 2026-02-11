# Release Notes - Hyro v1.1.2

**Release Date:** February 11, 2026  
**Repository:** https://github.com/marufsharia/hyro  
**Tag:** v1.1.2

---

## 🎉 What's New

### Beautiful Install Command with Laravel Prompts

We've completely redesigned the installation experience with stunning visuals and modern CLI interactions powered by Laravel Prompts.

#### Stunning ASCII Art Banner
```
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║   ██╗  ██╗██╗   ██╗██████╗  ██████╗                          ║
║   ██║  ██║╚██╗ ██╔╝██╔══██╗██╔═══██╗                         ║
║   ███████║ ╚████╔╝ ██████╔╝██║   ██║                         ║
║   ██╔══██║  ╚██╔╝  ██╔══██╗██║   ██║                         ║
║   ██║  ██║   ██║   ██║  ██║╚██████╔╝                         ║
║   ╚═╝  ╚═╝   ╚═╝   ╚═╝  ╚═╝ ╚═════╝                          ║
║                                                               ║
║        Enterprise Authorization System for Laravel           ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
```

### 4 Installation Modes

Choose the perfect installation mode for your needs:

1. **🚀 Silent Mode** - Zero configuration, perfect for production and CI/CD
   ```bash
   php artisan hyro:install --mode=silent --no-interaction
   ```

2. **📦 Minimal Mode** - Essential files only (Recommended)
   ```bash
   php artisan hyro:install --mode=minimal
   ```

3. **🎨 CRUD Mode** - Minimal + CRUD generator templates
   ```bash
   php artisan hyro:install --mode=crud
   ```

4. **🎁 Full Mode** - Everything including views and translations
   ```bash
   php artisan hyro:install --mode=full
   ```

### Interactive Installation Experience

- **Feature Highlights** - See what Hyro offers at a glance
- **Comparison Table** - Compare all installation modes
- **Interactive Selection** - Arrow-key navigation with hints
- **Installation Preview** - See what will be installed before confirming
- **Progress Indicators** - Beautiful spinners with step numbers [1/5], [2/5], etc.
- **Completion Screen** - Gorgeous bordered layout with next steps

### Enhanced Default Privileges

Added more comprehensive default privileges:
- API management (api.access, api.tokens)
- System management (system.logs)
- Enhanced wildcard privileges
- Better descriptions for all privileges

---

## 🔧 Improvements

### Laravel Prompts Integration

Integrated 9 Laravel Prompts features:
- `intro()` - Welcome banner
- `note()` - Information boxes
- `table()` - Comparison tables
- `select()` - Interactive selection
- `confirm()` - Custom confirmations
- `spin()` - Progress indicators
- `outro()` - Completion message
- `info()` - Success messages
- `warning()` - Warning indicators

### Visual Design

- Professional color scheme (cyan, green, yellow, blue)
- Clear visual hierarchy
- Emoji icons for better engagement
- Bordered layouts for important information
- Step-by-step progress tracking

### User Experience

- Helpful hints and descriptions
- Clear next steps after installation
- Resource links (documentation, support)
- Professional and modern appearance
- Delightful and engaging

---

## 🐛 Bug Fixes

### CRUD Generator Issues

1. **Fixed Missing Timestamps**
   - All CRUD-generated tables now include `created_at` and `updated_at` columns
   - Migration stub updated to include `$table->timestamps()` by default

2. **Fixed Permission Checks**
   - Changed from `user_id` check to `auth()->check()`
   - Updates and deletes now work correctly for authenticated users
   - Fixed in StoryManager, ArticleManager, ProjectManager

3. **Fixed View Method Error**
   - Changed `wire:click="view()"` to `wire:click="edit()"`
   - Fixed in all 8 frontend template stubs
   - No more "Method not found" errors

### API Authentication

1. **Added HasApiTokens Trait**
   - User model now includes Laravel Sanctum's `HasApiTokens` trait
   - API authentication working correctly

2. **Created API Resources**
   - Added `UserResource` for consistent user API responses
   - Added `TokenResource` for token management endpoints

3. **Fixed Sanctum Integration**
   - Published Sanctum configuration and migrations
   - API endpoints now fully functional

---

## 📚 Documentation

### New Documentation Files

1. **INSTALLATION_MODES.md** - Comprehensive guide to all installation modes
2. **BEAUTIFUL_INSTALL_COMMAND.md** - Visual documentation with examples
3. **API_SETUP_COMPLETE.md** - Complete API authentication guide

### Updated Documentation

- README.md - Updated installation section with new modes
- Added comparison tables
- Added usage examples
- Added CI/CD integration examples

---

## 🚀 Upgrade Guide

### From v1.0.x to v1.1.2

1. **Update Composer**
   ```bash
   composer update marufsharia/hyro
   ```

2. **Re-run Install (Optional)**
   ```bash
   php artisan hyro:install --mode=minimal --force
   ```

3. **Update User Model (If using API)**
   ```php
   use Laravel\Sanctum\HasApiTokens;
   
   class User extends Authenticatable
   {
       use HasHyroFeatures, HasApiTokens;
   }
   ```

4. **Publish Sanctum (If using API)**
   ```bash
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan migrate
   ```

---

## 💻 Installation

### New Installation

```bash
# Install via Composer
composer require marufsharia/hyro:^1.1.2

# Run interactive installer
php artisan hyro:install
```

### Specific Mode Installation

```bash
# Silent mode (production/CI-CD)
php artisan hyro:install --mode=silent --no-interaction

# Minimal mode (recommended)
php artisan hyro:install --mode=minimal

# CRUD mode (with templates)
php artisan hyro:install --mode=crud

# Full mode (everything)
php artisan hyro:install --mode=full
```

---

## 🔗 Links

- **Repository:** https://github.com/marufsharia/hyro
- **Documentation:** https://github.com/marufsharia/hyro#readme
- **Issues:** https://github.com/marufsharia/hyro/issues
- **Discussions:** https://github.com/marufsharia/hyro/discussions

---

## 📊 Statistics

- **Installation Modes:** 4
- **Laravel Prompts Features:** 9
- **Documentation Files:** 3 new, 1 updated
- **Bug Fixes:** 7
- **Lines of Code:** ~600 (InstallCommand.php)
- **Visual Elements:** ASCII art, tables, progress indicators, bordered layouts

---

## 🙏 Acknowledgments

Special thanks to:
- Laravel team for Laravel Prompts
- All contributors and users
- Community feedback and suggestions

---

## 📝 Changelog

### Added
- Professional install command with 4 modes
- Stunning ASCII art banner
- Interactive mode selection with comparison table
- Beautiful progress indicators with step numbers
- Gorgeous completion screen with next steps
- Laravel Prompts integration (9 features)
- Enhanced default privileges (API, system, wildcards)
- Comprehensive installation documentation
- API authentication resources (UserResource, TokenResource)

### Fixed
- Missing timestamps in CRUD-generated tables
- Permission checks in CRUD components
- View method errors in frontend templates
- API authentication issues
- Sanctum integration

### Changed
- Install command completely redesigned
- Installation experience now interactive and beautiful
- Default privileges expanded
- Documentation structure improved

### Improved
- User experience throughout installation
- Visual design and appearance
- Error messages and guidance
- Documentation clarity

---

## 🎯 Next Release Preview

Coming in v1.2.0:
- Enhanced CRUD generator with more templates
- Real-time notifications with broadcasting
- Advanced audit log filtering
- Plugin marketplace integration
- Performance optimizations

---

**Made with ❤️ by the Hyro team**

🌟 **Star us on GitHub if you find Hyro useful!**
