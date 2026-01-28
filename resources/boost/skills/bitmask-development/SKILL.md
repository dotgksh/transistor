---
name: bitmask-development
description: Work with bitmask flags in Laravel using the Transistor package, including creating enums, model setup, querying, and validation.
---

# Bitmask Development with Transistor

## When to Use This Skill

Activate when:
- Creating or modifying bitmask flag enums
- Setting up models with bitmask columns
- Writing queries that filter by bitmask flags
- Adding validation for bitmask inputs
- Using bitmask conditionals in Blade templates

## Creating Bitmask Flag Enums

Use `php artisan make:bitmask-flags` to generate enums interactively. The command will prompt for flag names.

Flags use bit-shifted values:
- Flag 1: `1 << 0` (1)
- Flag 2: `1 << 1` (2)
- Flag 3: `1 << 2` (4)
- Flag 4: `1 << 3` (8)

Example enum:

```php
enum Permission: int
{
    case Read = 1 << 0;    // 1
    case Write = 1 << 1;   // 2
    case Delete = 1 << 2;  // 4
    case Admin = 1 << 3;   // 8
}
```

## Model Configuration

Add the trait and cast to your model:

```php
use Gksh\Transistor\Casts\BitmaskCast;
use Gksh\Transistor\Concerns\HasBitmaskScopes;

class User extends Model
{
    use HasBitmaskScopes;

    protected function casts(): array
    {
        return [
            'permissions' => BitmaskCast::class,
            // Or specify size: BitmaskCast::class.':tiny'
        ];
    }
}
```

Cast size options: `tiny`, `small`, `medium`, or `default` (32-bit).

## Migration Examples

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->bitmask('permissions');        // 32-bit unsigned integer, default 0
    $table->tinyBitmask('settings');       // 8-bit (up to 8 flags)
    $table->smallBitmask('preferences');   // 16-bit (up to 16 flags)
    $table->mediumBitmask('features');     // 24-bit (up to 24 flags)
});
```

## Query Scope Examples

Filter records by bitmask flags:

```php
// Has a specific flag
User::whereHasBitmaskFlag('permissions', Permission::Admin)->get();

// Has ALL specified flags
User::whereHasAllBitmaskFlags('permissions', [Permission::Read, Permission::Write])->get();

// Has ANY of the specified flags
User::whereHasAnyBitmaskFlag('permissions', [Permission::Admin, Permission::Delete])->get();

// Does NOT have a specific flag
User::whereDoesntHaveBitmaskFlag('permissions', Permission::Admin)->get();

// Does NOT have ANY of the specified flags
User::whereDoesntHaveAnyBitmaskFlag('permissions', [Permission::Admin, Permission::Delete])->get();
```

## Validation Examples

In a Form Request:

```php
use Gksh\Transistor\Rules\ValidBitmask;

public function rules(): array
{
    return [
        // Basic validation (any non-negative integer)
        'permissions' => ['required', new ValidBitmask],

        // With enum constraint (validates against max possible value)
        'permissions' => ['required', new ValidBitmask(Permission::class)],

        // Using inline rule syntax
        'permissions' => ['required', 'bitmask:'.Permission::class],
    ];
}
```

## Blade Directive Examples

Check flags in Blade templates:

```blade
@hasBitmaskFlag($user->permissions, Permission::Admin)
    <p>User is an admin</p>
@endhasBitmaskFlag

@hasAnyBitmaskFlag($user->permissions, [Permission::Read, Permission::Write])
    <p>User can read or write</p>
@endhasAnyBitmaskFlag

@hasAllBitmaskFlags($user->permissions, [Permission::Read, Permission::Write])
    <p>User can read AND write</p>
@endhasAllBitmaskFlags
```

## Inspecting Bitmask Values

Use the inspect command to visualize flags:

```bash
# Show all flags for an enum
php artisan bitmask:inspect Permission

# Analyze a specific value
php artisan bitmask:inspect Permission 7
```

This displays a table with case names, decimal values, and binary representation with colored bits.

## Working with Bitmask Objects

The cast returns a `Gksh\Bitmask\Bitmask` object:

```php
$user = User::find(1);

// Check if a flag is set
$user->permissions->has(Permission::Admin);

// Get the integer value
$user->permissions->value();
```
