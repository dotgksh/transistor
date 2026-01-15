# Transistor

Laravel integration for [gksh/bitmask](https://github.com/gksh/bitmask) - providing Eloquent casting, query scopes, validation, Blade directives, and migration macros for working with bitmask flags.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gksh/transistor.svg?style=flat-square)](https://packagist.org/packages/gksh/transistor)
[![PHP Version](https://img.shields.io/packagist/php-v/gksh/transistor.svg?style=flat-square)](https://packagist.org/packages/gksh/transistor)
[![License](https://img.shields.io/packagist/l/gksh/transistor.svg?style=flat-square)](https://packagist.org/packages/gksh/transistor)

## Installation

```bash
composer require gksh/transistor
```

The service provider is auto-discovered by Laravel.

## Quick Start

```php
// 1. Generate a bitmask enum
php artisan make:bitmask-flags Permission

// 2. Add migration macro
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->bitmask('permissions');
});

// 3. Configure your model
use Gksh\Transistor\Casts\BitmaskCast;
use Gksh\Transistor\Concerns\HasBitmaskScopes;

class User extends Model
{
    use HasBitmaskScopes;

    protected function casts(): array
    {
        return [
            'permissions' => BitmaskCast::class,
        ];
    }
}

// 4. Use it
$user->permissions = $user->permissions
    ->set(Permission::Read)
    ->set(Permission::Write);

User::whereHasBitmaskFlag('permissions', Permission::Admin)->get();
```

## Artisan Commands

### make:bitmask-flags

Generate a bitmask flags enum interactively:

```bash
php artisan make:bitmask-flags
php artisan make:bitmask-flags Permission
```

Creates an enum in `app/Enums` with bit-shifted values:

```php
namespace App\Enums;

enum Permission: int
{
    case Read = 1 << 0;    // 1
    case Write = 1 << 1;   // 2
    case Delete = 1 << 2;  // 4
    case Admin = 1 << 3;   // 8
}
```

### bitmask:inspect

Display bitmask flags as a lookup table:

```bash
php artisan bitmask:inspect "App\Enums\Permission"
php artisan bitmask:inspect Permission 13
```

```
+--------+---------+-----------------+
| Case   | Decimal | Binary          |
+--------+---------+-----------------+
| Read   | 1       | 0 0 0 0 0 0 0 1 |
| Write  | 2       | 0 0 0 0 0 0 1 0 |
| Delete | 4       | 0 0 0 0 0 1 0 0 |
| Admin  | 8       | 0 0 0 0 1 0 0 0 |
+--------+---------+-----------------+
| Value  | 13      | 0 0 0 0 1 1 0 1 |
+--------+---------+-----------------+
```

When a value is provided, active bits are highlighted in green.

## Eloquent Cast

Cast database integers to `Bitmask` objects:

```php
use Gksh\Transistor\Casts\BitmaskCast;

protected function casts(): array
{
    return [
        'permissions' => BitmaskCast::class,              // 32-bit (default)
        'settings' => BitmaskCast::class.':tiny',         // 8-bit (0-255)
        'features' => BitmaskCast::class.':small',        // 16-bit (0-65,535)
        'options' => BitmaskCast::class.':medium',        // 24-bit (0-16,777,215)
    ];
}
```

The cast returns a `Gksh\Bitmask\Bitmask` object with methods like `set()`, `unset()`, `toggle()`, `has()`, and `value()`.

## Query Scopes

Add the `HasBitmaskScopes` trait to your model:

```php
use Gksh\Transistor\Concerns\HasBitmaskScopes;

class User extends Model
{
    use HasBitmaskScopes;
}
```

### Available Scopes

```php
// Filter where flag IS set
User::whereHasBitmaskFlag('permissions', Permission::Read)->get();

// Filter where ALL flags are set
User::whereHasAllBitmaskFlags('permissions', [Permission::Read, Permission::Write])->get();

// Filter where ANY flag is set
User::whereHasAnyBitmaskFlag('permissions', [Permission::Write, Permission::Admin])->get();

// Filter where flag is NOT set
User::whereDoesntHaveBitmaskFlag('permissions', Permission::Admin)->get();

// Filter where NONE of the flags are set
User::whereDoesntHaveAnyBitmaskFlag('permissions', [Permission::Read, Permission::Write])->get();
```

Scopes can be chained:

```php
User::whereHasBitmaskFlag('permissions', Permission::Read)
    ->whereDoesntHaveBitmaskFlag('permissions', Permission::Admin)
    ->get();
```

## Migration Macros

Create bitmask columns with the appropriate integer size:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->bitmask('permissions');       // unsigned integer (32-bit)
    $table->tinyBitmask('settings');      // unsigned tinyInteger (8-bit)
    $table->smallBitmask('features');     // unsigned smallInteger (16-bit)
    $table->mediumBitmask('options');     // unsigned mediumInteger (24-bit)
});
```

All macros set a default value of `0`.

## Validation

### As a Rule Object

```php
use Gksh\Transistor\Rules\ValidBitmask;

$validated = $request->validate([
    'permissions' => ['required', new ValidBitmask()],
    'role_flags' => ['required', new ValidBitmask(Permission::class)],
]);
```

### As a String Rule

```php
$validated = $request->validate([
    'permissions' => 'required|bitmask',
    'role_flags' => 'required|bitmask:App\Enums\Permission',
]);
```

When an enum class is provided, the rule ensures the value doesn't exceed the maximum possible combination of all flags.

## Blade Directives

```blade
@hasBitmaskFlag($user->permissions, Permission::Admin)
    <span>Administrator</span>
@endif

@hasAnyBitmaskFlag($user->permissions, [Permission::Write, Permission::Admin])
    <button>Edit</button>
@endif

@hasAllBitmaskFlags($user->permissions, [Permission::Read, Permission::Write])
    <span>Full Access</span>
@endif
```

## Requirements

- PHP 8.2+
- Laravel 11.x or 12.x

## License

MIT
