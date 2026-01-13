<?php

declare(strict_types=1);

namespace Gksh\Transistor\Concerns;

use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

use function Gksh\Bitmask\Support\maskValue;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasBitmaskScopes
{
    /**
     * Scope to filter records where the bitmask column has a specific flag set.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWhereHasBitmaskFlag(Builder $query, string $column, int|BackedEnum|UnitEnum $flag): Builder
    {
        $value = maskValue($flag);

        return $query->whereRaw("{$column} & ? = ?", [$value, $value]);
    }

    /**
     * Scope to filter records where the bitmask column has ALL specified flags set.
     *
     * @param  Builder<static>  $query
     * @param  array<int|BackedEnum|UnitEnum>  $flags
     * @return Builder<static>
     */
    public function scopeWhereHasAllBitmaskFlags(Builder $query, string $column, array $flags): Builder
    {
        $value = $this->combinedMaskValue($flags);

        return $query->whereRaw("{$column} & ? = ?", [$value, $value]);
    }

    /**
     * Scope to filter records where the bitmask column has ANY of the specified flags set.
     *
     * @param  Builder<static>  $query
     * @param  array<int|BackedEnum|UnitEnum>  $flags
     * @return Builder<static>
     */
    public function scopeWhereHasAnyBitmaskFlag(Builder $query, string $column, array $flags): Builder
    {
        $value = $this->combinedMaskValue($flags);

        return $query->whereRaw("{$column} & ? != 0", [$value]);
    }

    /**
     * Scope to filter records where the bitmask column does NOT have a specific flag set.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWhereDoesntHaveBitmaskFlag(Builder $query, string $column, int|BackedEnum|UnitEnum $flag): Builder
    {
        $value = maskValue($flag);

        return $query->whereRaw("{$column} & ? != ?", [$value, $value]);
    }

    /**
     * Scope to filter records where the bitmask column does NOT have ANY of the specified flags set.
     *
     * @param  Builder<static>  $query
     * @param  array<int|BackedEnum|UnitEnum>  $flags
     * @return Builder<static>
     */
    public function scopeWhereDoesntHaveAnyBitmaskFlag(Builder $query, string $column, array $flags): Builder
    {
        $value = $this->combinedMaskValue($flags);

        return $query->whereRaw("{$column} & ? = 0", [$value]);
    }

    /**
     * @param  array<int|BackedEnum|UnitEnum>  $flags
     */
    private function combinedMaskValue(array $flags): int
    {
        return array_reduce(
            array_map(maskValue(...), $flags),
            fn (int $carry, int $value): int => $carry | $value,
            0
        );
    }
}
