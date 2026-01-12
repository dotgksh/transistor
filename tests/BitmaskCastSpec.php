<?php

declare(strict_types=1);

use Gksh\Bitmask\Bitmask;
use Gksh\Transistor\Tests\Fixtures\Permission;
use Gksh\Transistor\Tests\Fixtures\User;

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/database/migrations');
});

it('casts integer to bitmask on retrieval', function () {
    User::create(['permissions' => 5, 'settings' => 3]);

    $user = User::first();

    expect($user->permissions)
        ->toBeInstanceOf(Bitmask::class)
        ->and($user->permissions->value())->toBe(5);
});

it('casts bitmask to integer on storage', function () {
    $bitmask = Bitmask::make()
        ->set(Permission::Read)
        ->set(Permission::Delete);

    User::create(['permissions' => $bitmask, 'settings' => 0]);

    $this->assertDatabaseHas('users', [
        'permissions' => 5,
    ]);
});

it('handles null values', function () {
    User::unguard();
    $user = new User(['permissions' => null, 'settings' => null]);
    User::reguard();

    expect($user->permissions)->toBeNull();
});

it('respects size parameter for tiny bitmask', function () {
    User::create(['permissions' => 0, 'settings' => 127]);

    $user = User::first();

    expect($user->settings)
        ->toBeInstanceOf(Bitmask::class)
        ->and($user->settings->size()->value)->toBe(8);
});

it('preserves bitmask operations through save cycle', function () {
    $user = User::create(['permissions' => 0, 'settings' => 0]);

    $user->permissions = $user->permissions
        ->set(Permission::Read)
        ->set(Permission::Write);

    $user->save();

    $freshUser = User::find($user->id);

    expect($freshUser->permissions->has(Permission::Read))->toBeTrue()
        ->and($freshUser->permissions->has(Permission::Write))->toBeTrue()
        ->and($freshUser->permissions->has(Permission::Delete))->toBeFalse();
});
