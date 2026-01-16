<?php

declare(strict_types=1);

namespace Gksh\Transistor\Casts;

use Gksh\Bitmask\Bitmask;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<Bitmask, Bitmask|int>
 */
final class BitmaskCast implements CastsAttributes
{
    /**
     * @param  'tiny'|'small'|'medium'|'default'  $size
     */
    public function __construct(
        protected string $size = 'default',
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Bitmask
    {
        if ($value === null) {
            return null;
        }

        $intValue = is_numeric($value) ? (int) $value : 0;

        return match ($this->size) {
            'tiny' => Bitmask::tiny($intValue),
            'small' => Bitmask::small($intValue),
            'medium' => Bitmask::medium($intValue),
            default => Bitmask::make($intValue),
        };
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return match (true) {
            $value === null => null,
            $value instanceof Bitmask => $value->value(),
            default => (int) $value,
        };
    }
}
