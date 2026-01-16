<?php

declare(strict_types=1);

use Gksh\Transistor\Tests\Fixtures\Permission;
use Gksh\Transistor\Tests\Fixtures\User;

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/database/migrations');

    // Create test users with different permission combinations
    User::create(['permissions' => Permission::Read->value, 'settings' => 0]);                                  // 1
    User::create(['permissions' => Permission::Read->value | Permission::Write->value, 'settings' => 0]);       // 3
    User::create(['permissions' => Permission::Admin->value, 'settings' => 0]);                                  // 8
    User::create(['permissions' => Permission::Read->value | Permission::Admin->value, 'settings' => 0]);       // 9
    User::create(['permissions' => 0, 'settings' => 0]);                                                          // 0
});

it('filters by single flag', function () {
    $users = User::whereHasBitmaskFlag('permissions', Permission::Read)->get();

    expect($users)->toHaveCount(3);
});

it('filters by all flags', function () {
    $users = User::whereHasAllBitmaskFlags('permissions', [Permission::Read, Permission::Write])->get();

    expect($users)->toHaveCount(1)
        ->and($users->first()->permissions->value())->toBe(3);
});

it('filters by any flag', function () {
    $users = User::whereHasAnyBitmaskFlag('permissions', [Permission::Write, Permission::Admin])->get();

    expect($users)->toHaveCount(3);
});

it('filters by missing flag', function () {
    $users = User::whereDoesntHaveBitmaskFlag('permissions', Permission::Admin)->get();

    expect($users)->toHaveCount(3);
});

it('filters by missing any flags', function () {
    $users = User::whereDoesntHaveAnyBitmaskFlag('permissions', [Permission::Read, Permission::Write])->get();

    expect($users)->toHaveCount(2);
});

it('works with integer values', function () {
    $users = User::whereHasBitmaskFlag('permissions', 1)->get();

    expect($users)->toHaveCount(3);
});

it('can chain multiple scopes', function () {
    $users = User::whereHasBitmaskFlag('permissions', Permission::Read)
        ->whereDoesntHaveBitmaskFlag('permissions', Permission::Admin)
        ->get();

    expect($users)->toHaveCount(2);
});
