<?php

declare(strict_types=1);

namespace Gksh\Transistor\Tests\Fixtures;

use Gksh\Transistor\Casts\BitmaskCast;
use Gksh\Transistor\Concerns\HasBitmaskScopes;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $permissions
 * @property int|null $settings
 */
final class User extends Model
{
    use HasBitmaskScopes;

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'permissions' => BitmaskCast::class,
            'settings' => BitmaskCast::class.':tiny',
        ];
    }
}
