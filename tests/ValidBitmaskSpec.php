<?php

declare(strict_types=1);

use Gksh\Transistor\Rules\ValidBitmask;
use Gksh\Transistor\Tests\Fixtures\Permission;
use Illuminate\Support\Facades\Validator;

it('passes for valid integer bitmask without enum', function () {
    $rule = new ValidBitmask;

    expect($rule->passes('permissions', 0))->toBeTrue()
        ->and($rule->passes('permissions', 15))->toBeTrue()
        ->and($rule->passes('permissions', 255))->toBeTrue();
});

it('fails for negative values', function () {
    $rule = new ValidBitmask;

    expect($rule->passes('permissions', -1))->toBeFalse();
});

it('fails for non-numeric values', function () {
    $rule = new ValidBitmask;

    expect($rule->passes('permissions', 'invalid'))->toBeFalse()
        ->and($rule->passes('permissions', []))->toBeFalse()
        ->and($rule->passes('permissions', null))->toBeFalse();
});

it('passes for valid bitmask within enum range', function () {
    $rule = new ValidBitmask(Permission::class);

    // All flags: Read(1) | Write(2) | Delete(4) | Admin(8) = 15
    expect($rule->passes('permissions', 0))->toBeTrue()
        ->and($rule->passes('permissions', 1))->toBeTrue()
        ->and($rule->passes('permissions', 7))->toBeTrue()
        ->and($rule->passes('permissions', 15))->toBeTrue();
});

it('fails for bitmask exceeding enum range', function () {
    $rule = new ValidBitmask(Permission::class);

    // Max valid is 15, so 16 should fail
    expect($rule->passes('permissions', 16))->toBeFalse()
        ->and($rule->passes('permissions', 255))->toBeFalse();
});

it('works as string validation rule', function () {
    $validator = Validator::make(
        ['permissions' => 7],
        ['permissions' => 'bitmask:'.Permission::class]
    );

    expect($validator->passes())->toBeTrue();

    $validator = Validator::make(
        ['permissions' => 16],
        ['permissions' => 'bitmask:'.Permission::class]
    );

    expect($validator->passes())->toBeFalse();
});

it('accepts numeric strings', function () {
    $rule = new ValidBitmask;

    expect($rule->passes('permissions', '15'))->toBeTrue()
        ->and($rule->passes('permissions', '0'))->toBeTrue();
});
