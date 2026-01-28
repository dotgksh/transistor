## Transistor (Bitmask Flags)

This package provides bitmask flag support for Laravel Eloquent models.

### Artisan Commands

- `php artisan make:bitmask-flags {name}` - Generate a bitmask flags enum
- `php artisan bitmask:inspect {enum} {value?}` - Display bitmask flags as a lookup table

### Model Setup

1. Add the `HasBitmaskScopes` trait for query scopes
2. Cast bitmask columns using `BitmaskCast::class` with optional size parameter

### Migration Macros

- `$table->bitmask('column')` - 32-bit (up to 32 flags)
- `$table->tinyBitmask('column')` - 8-bit (up to 8 flags)
- `$table->smallBitmask('column')` - 16-bit (up to 16 flags)
- `$table->mediumBitmask('column')` - 24-bit (up to 24 flags)

### Query Scopes

- `whereHasBitmaskFlag($column, $flag)`
- `whereHasAllBitmaskFlags($column, $flags)`
- `whereHasAnyBitmaskFlag($column, $flags)`
- `whereDoesntHaveBitmaskFlag($column, $flag)`
- `whereDoesntHaveAnyBitmaskFlag($column, $flags)`

### Validation

Use `ValidBitmask` rule class or inline `bitmask` rule with optional enum class.

### Blade Directives

- `@@hasBitmaskFlag($bitmask, Flag::Case)`
- `@@hasAnyBitmaskFlag($bitmask, [Flag::A, Flag::B])`
- `@@hasAllBitmaskFlags($bitmask, [Flag::A, Flag::B])`
