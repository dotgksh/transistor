<?php

declare(strict_types=1);

use Gksh\Transistor\Tests\Fixtures\Permission;
use Gksh\Transistor\Tests\Fixtures\UnitPermission;
use Symfony\Component\Console\Helper\TableSeparator;

it('displays correct table for a backed enum', function () {
    $this->artisan('bitmask:inspect', ['enum' => Permission::class])
        ->expectsTable(
            ['Case', 'Decimal', 'Binary'],
            [
                ['Read', '1', '0 0 0 0 0 0 0 1'],
                ['Write', '2', '0 0 0 0 0 0 1 0'],
                ['Delete', '4', '0 0 0 0 0 1 0 0'],
                ['Admin', '8', '0 0 0 0 1 0 0 0'],
            ]
        )
        ->assertSuccessful();
});

it('displays correct table for a unit enum', function () {
    $this->artisan('bitmask:inspect', ['enum' => UnitPermission::class])
        ->expectsTable(
            ['Case', 'Decimal', 'Binary'],
            [
                ['Read', '1', '0 0 0 0 0 0 0 1'],
                ['Write', '2', '0 0 0 0 0 0 1 0'],
                ['Delete', '4', '0 0 0 0 0 1 0 0'],
                ['Admin', '8', '0 0 0 0 1 0 0 0'],
            ]
        )
        ->assertSuccessful();
});

it('displays value row with correct decimal and binary', function (int $value, string $expectedBinary) {
    $this->artisan('bitmask:inspect', ['enum' => Permission::class, 'value' => $value])
        ->expectsTable(
            ['Case', 'Decimal', 'Binary'],
            [
                ['Read', '1', '0 0 0 0 0 0 0 1'],
                ['Write', '2', '0 0 0 0 0 0 1 0'],
                ['Delete', '4', '0 0 0 0 0 1 0 0'],
                ['Admin', '8', '0 0 0 0 1 0 0 0'],
                new TableSeparator,
                ['Value', (string) $value, $expectedBinary],
            ]
        )
        ->assertSuccessful();
})->with([
    'single flag (Read)' => [1, '0 0 0 0 0 0 0 1'],
    'two flags (Read + Delete)' => [5, '0 0 0 0 0 1 0 1'],
    'three flags (Read + Write + Admin)' => [11, '0 0 0 0 1 0 1 1'],
    'all flags' => [15, '0 0 0 0 1 1 1 1'],
    'zero (no flags)' => [0, '0 0 0 0 0 0 0 0'],
]);

it('fails for non-existent enum', function () {
    $this->artisan('bitmask:inspect', ['enum' => 'NonExistentEnum'])
        ->expectsOutputToContain('not found')
        ->assertFailed();
});

it('fails for invalid value', function () {
    $this->artisan('bitmask:inspect', ['enum' => Permission::class, 'value' => 'invalid'])
        ->expectsOutputToContain('non-negative integer')
        ->assertFailed();
});
