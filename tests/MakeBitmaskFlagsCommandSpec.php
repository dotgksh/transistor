<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Laravel\Prompts\Prompt;

beforeEach(function () {
    $this->enumPath = app_path('Enums');

    if (File::isDirectory($this->enumPath)) {
        File::deleteDirectory($this->enumPath);
    }
});

afterEach(function () {
    if (File::isDirectory($this->enumPath)) {
        File::deleteDirectory($this->enumPath);
    }

    Prompt::fallbackWhen(false);
});

it('creates enum with correct structure', function () {
    Prompt::fallbackWhen(true);

    $this->artisan('make:bitmask-flags', ['name' => 'TestPermission'])
        ->expectsQuestion('Flag 1', 'Read')
        ->expectsQuestion('Flag 2', 'Write')
        ->expectsQuestion('Flag 3', '')
        ->assertSuccessful();

    $filePath = app_path('Enums/TestPermission.php');
    expect(File::exists($filePath))->toBeTrue();

    $content = File::get($filePath);
    expect($content)
        ->toContain('namespace App\Enums;')
        ->toContain('enum TestPermission: int')
        ->toContain('case Read = 1 << 0;')
        ->toContain('case Write = 1 << 1;');
});

it('creates enum in Enums namespace', function () {
    Prompt::fallbackWhen(true);

    $this->artisan('make:bitmask-flags', ['name' => 'NamespaceEnum'])
        ->expectsQuestion('Flag 1', 'First')
        ->expectsQuestion('Flag 2', '')
        ->assertSuccessful();

    $content = File::get(app_path('Enums/NamespaceEnum.php'));
    expect($content)->toContain('namespace App\Enums;');
});

it('fails when no flags are provided', function () {
    Prompt::fallbackWhen(true);

    $this->artisan('make:bitmask-flags', ['name' => 'EmptyEnum'])
        ->expectsQuestion('Flag 1', '');

    // File should not be created when no flags provided
    expect(File::exists(app_path('Enums/EmptyEnum.php')))->toBeFalse();
});

it('converts flag names to studly case', function () {
    Prompt::fallbackWhen(true);

    $this->artisan('make:bitmask-flags', ['name' => 'StudlyEnum'])
        ->expectsQuestion('Flag 1', 'some_flag')
        ->expectsQuestion('Flag 2', 'another-flag')
        ->expectsQuestion('Flag 3', '')
        ->assertSuccessful();

    $content = File::get(app_path('Enums/StudlyEnum.php'));
    expect($content)
        ->toContain('case SomeFlag = 1 << 0;')
        ->toContain('case AnotherFlag = 1 << 1;');
});

it('assigns correct bit shift values', function () {
    Prompt::fallbackWhen(true);

    $this->artisan('make:bitmask-flags', ['name' => 'ManyFlags'])
        ->expectsQuestion('Flag 1', 'First')
        ->expectsQuestion('Flag 2', 'Second')
        ->expectsQuestion('Flag 3', 'Third')
        ->expectsQuestion('Flag 4', 'Fourth')
        ->expectsQuestion('Flag 5', '')
        ->assertSuccessful();

    $content = File::get(app_path('Enums/ManyFlags.php'));
    expect($content)
        ->toContain('case First = 1 << 0;')
        ->toContain('case Second = 1 << 1;')
        ->toContain('case Third = 1 << 2;')
        ->toContain('case Fourth = 1 << 3;');
});
