# Beautiful Install Command with Laravel Prompts

Hyro's install command features a stunning, modern interface powered by Laravel Prompts, providing an exceptional developer experience.

## 🎨 Visual Features

### Beautiful ASCII Art Banner

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

### Interactive Mode Selection

Beautiful table showing all installation modes:

```
┌──────────┬────────┬──────────────────────────────┬─────────────────────────┐
│ Mode     │ Size   │ Files                        │ Best For                │
├──────────┼────────┼──────────────────────────────┼─────────────────────────┤
│ Silent   │ ~2MB   │ Config, Migrations, Assets   │ Production, CI/CD       │
│ Minimal  │ ~2MB   │ Config, Migrations, Assets   │ Most Projects           │
│ CRUD     │ ~5MB   │ Minimal + CRUD Stubs         │ Admin Panels            │
│ Full     │ ~10MB  │ Everything                   │ Development             │
└──────────┴────────┴──────────────────────────────┴─────────────────────────┘
```

### Progress Indicators

Beautiful spinners for each installation step:

```
⠋ [1/5] Publishing configuration...
⠙ [2/5] Publishing migrations...
⠹ [3/5] Publishing compiled assets...
⠸ [4/5] Running migrations...
⠼ [5/5] Seeding initial data...
```

### Completion Screen

Stunning completion screen with next steps:

```
╔═══════════════════════════════════════════════════════════════╗
║ Next Steps                                                    ║
╠═══════════════════════════════════════════════════════════════╣
║                                                               ║
║ 1. Add trait to your User model:                             ║
║    use Marufsharia\Hyro\Traits\HasHyroFeatures;              ║
║                                                               ║
║ 2. Review configuration:                                     ║
║    php artisan config:clear                                  ║
║    nano config/hyro.php                                      ║
║                                                               ║
║ 3. Create your first admin user:                             ║
║    php artisan hyro:user:create                              ║
║                                                               ║
║ 4. Run health check:                                         ║
║    php artisan hyro:health-check                             ║
║                                                               ║
╠═══════════════════════════════════════════════════════════════╣
║ Resources                                                     ║
╠═══════════════════════════════════════════════════════════════╣
║                                                               ║
║ 📚 Documentation:                                             ║
║    https://github.com/marufsharia/hyro                       ║
║                                                               ║
║ 💬 Support & Issues:                                          ║
║    https://github.com/marufsharia/hyro/issues                ║
║                                                               ║
║ 🌟 Star us on GitHub if you find Hyro useful!                ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝

  🎉 Happy coding with Hyro!
```

## 🚀 Usage Examples

### Interactive Installation (Recommended)

```bash
php artisan hyro:install
```

**Experience:**
1. Beautiful ASCII art welcome banner
2. Feature highlights with emojis
3. Interactive mode selection with table
4. Installation details preview
5. Confirmation prompt
6. Progress indicators for each step
7. Stunning completion screen with next steps

### Silent Installation

```bash
php artisan hyro:install --mode=silent --no-interaction
```

**Experience:**
- Minimal output
- Fast installation
- Perfect for CI/CD

### Specific Mode Installation

```bash
# Minimal mode
php artisan hyro:install --mode=minimal

# CRUD mode
php artisan hyro:install --mode=crud

# Full mode
php artisan hyro:install --mode=full
```

## 🎯 Laravel Prompts Features Used

### 1. `intro()` - Welcome Message
```php
intro('🚀 Welcome to Hyro Installation');
```

### 2. `note()` - Information Boxes
```php
note(
    "Hyro is a comprehensive authorization system that provides:\n\n" .
    "  🔐 Advanced role-based access control (RBAC)\n" .
    "  ⚡ Powerful CRUD generator with 10+ templates\n" .
    "  🔔 Built-in notification system"
);
```

### 3. `table()` - Comparison Tables
```php
table(
    headers: ['Mode', 'Size', 'Files', 'Best For'],
    rows: [
        ['Silent', '~2MB', 'Config, Migrations, Assets', 'Production, CI/CD'],
        ['Minimal', '~2MB', 'Config, Migrations, Assets', 'Most Projects'],
    ]
);
```

### 4. `select()` - Interactive Selection
```php
select(
    label: 'Select installation mode',
    options: [
        'minimal' => '📦 Minimal - Essential files only (Recommended)',
        'crud' => '🎨 CRUD - Minimal + CRUD templates',
        'full' => '🎁 Full - All publishable assets',
        'silent' => '🚀 Silent - Zero interaction, auto-configure',
    ],
    default: 'minimal',
    hint: 'Use arrow keys to navigate, Enter to select'
);
```

### 5. `confirm()` - Confirmation Prompts
```php
confirm(
    label: 'Ready to install Hyro?',
    default: true,
    yes: 'Yes, install now',
    no: 'No, cancel',
    hint: 'This will publish files and run migrations'
);
```

### 6. `spin()` - Progress Indicators
```php
spin(
    callback: fn() => $this->publishConfig(),
    message: '[1/5] Publishing configuration...'
);
```

### 7. `outro()` - Completion Message
```php
outro('✨ Installation Complete!');
```

### 8. `info()` - Success Messages
```php
info('Installation completed successfully!');
```

### 9. `warning()` - Warning Messages
```php
warning('Migrations table not found. Creating it...');
```

## 🎨 Color Scheme

The install command uses a carefully chosen color palette:

- **Cyan** - Headers, borders, primary elements
- **Green** - Success messages, commands
- **Yellow** - Section titles, important info
- **Blue** - Links, URLs
- **White** - Main content
- **Gray** - Secondary content, hints

## 📱 Responsive Design

The command adapts to different terminal widths and automatically:
- Adjusts table column widths
- Wraps long text appropriately
- Maintains visual hierarchy
- Ensures readability

## 🔄 Interactive Flow

```
┌─────────────────────────────────────┐
│  1. Beautiful Welcome Banner        │
│     ↓                               │
│  2. Feature Highlights              │
│     ↓                               │
│  3. Mode Comparison Table           │
│     ↓                               │
│  4. Interactive Mode Selection      │
│     ↓                               │
│  5. Installation Details Preview    │
│     ↓                               │
│  6. Confirmation Prompt             │
│     ↓                               │
│  7. Progress Indicators (Spinners)  │
│     ↓                               │
│  8. Completion Screen               │
│     ↓                               │
│  9. Next Steps & Resources          │
└─────────────────────────────────────┘
```

## 💡 Best Practices

### For Users
1. **Use interactive mode** for first-time installation
2. **Read the welcome screen** to understand features
3. **Review the comparison table** before selecting mode
4. **Follow next steps** shown in completion screen

### For CI/CD
1. **Use silent mode** with `--no-interaction`
2. **Specify mode explicitly** with `--mode=silent`
3. **Use `--force`** to skip confirmations
4. **Capture output** for logging

### For Development
1. **Use CRUD or Full mode** for development
2. **Test different modes** to understand differences
3. **Customize published files** as needed
4. **Upgrade modes** when requirements change

## 🎭 Comparison: Before vs After

### Before (Old Command)
```
╔══════════════════════════════════════════════════════════╗
║                    Hyro Installation                     ║
╠══════════════════════════════════════════════════════════╣
║ Welcome to Hyro - Enterprise Authorization System        ║
║ This installer will set up the package in your app.      ║
╚══════════════════════════════════════════════════════════╝

Installation Steps:
  1. Publish configuration and assets
  2. Run database migrations
  3. Seeding initial data

📦 Publishing assets...
🗄️  Running migrations...
🌱 Seeding initial data...
✅ Installation completed
```

### After (New Command)
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

🚀 Welcome to Hyro Installation

┌ Hyro is a comprehensive authorization system that provides: ─┐
│                                                              │
│   🔐 Advanced role-based access control (RBAC)              │
│   ⚡ Powerful CRUD generator with 10+ templates             │
│   🔔 Built-in notification system                           │
│   📊 Enterprise audit logging                               │
│   🔌 Extensible plugin system                               │
│   🚀 RESTful API with Sanctum                               │
│   🎨 Beautiful admin interface                              │
│                                                              │
└──────────────────────────────────────────────────────────────┘

[Interactive table and selection...]

⠋ [1/5] Publishing configuration...
⠙ [2/5] Publishing migrations...
⠹ [3/5] Publishing compiled assets...
⠸ [4/5] Running migrations...
⠼ [5/5] Seeding initial data...

✨ Installation Complete!

[Beautiful completion screen with next steps...]

  🎉 Happy coding with Hyro!
```

## 🌟 Key Improvements

1. **Visual Appeal** - Stunning ASCII art and beautiful formatting
2. **Information Density** - More information in less space
3. **User Guidance** - Clear next steps and resources
4. **Professional Look** - Enterprise-grade appearance
5. **Better UX** - Intuitive flow and helpful hints
6. **Modern Feel** - Uses latest Laravel Prompts features
7. **Accessibility** - Clear visual hierarchy and readable text
8. **Engagement** - Emojis and colors make it more engaging

## 📸 Screenshots

> Note: Run `php artisan hyro:install` to see the beautiful interface in action!

## 🎓 Learning Resources

- [Laravel Prompts Documentation](https://laravel.com/docs/prompts)
- [Hyro Installation Guide](INSTALLATION_MODES.md)
- [Hyro GitHub Repository](https://github.com/marufsharia/hyro)

---

**Made with ❤️ by the Hyro team**
