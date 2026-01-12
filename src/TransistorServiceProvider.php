<?php

declare(strict_types=1);

namespace Gksh\Transistor;

use Gksh\Bitmask\Bitmask;
use Gksh\Transistor\Commands\MakeBitmaskFlagsCommand;
use Gksh\Transistor\Rules\ValidBitmask;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

final class TransistorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerBladeDirectives();
        $this->registerValidationRules();
        $this->registerBlueprintMacros();
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeBitmaskFlagsCommand::class,
            ]);
        }
    }

    private function registerBladeDirectives(): void
    {
        Blade::if('hasBitmaskFlag', fn (Bitmask $bitmask, int|\BackedEnum|\UnitEnum $flag): bool => $bitmask->has($flag));

        /** @param array<int|\BackedEnum|\UnitEnum> $flags */
        Blade::if('hasAnyBitmaskFlag', fn (Bitmask $bitmask, array $flags): bool => array_any(
            $flags,
            fn (int|\BackedEnum|\UnitEnum $flag): bool => $bitmask->has($flag), // @phpstan-ignore argument.type
        ));

        /** @param array<int|\BackedEnum|\UnitEnum> $flags */
        Blade::if('hasAllBitmaskFlags', fn (Bitmask $bitmask, array $flags): bool => array_all(
            $flags,
            fn (int|\BackedEnum|\UnitEnum $flag): bool => $bitmask->has($flag), // @phpstan-ignore argument.type
        ));
    }

    private function registerValidationRules(): void
    {
        Validator::extend('bitmask', function (string $attribute, mixed $value, array $parameters): bool {
            /** @var class-string<\BackedEnum|\UnitEnum>|null $enumClass */
            $enumClass = $parameters[0] ?? null;

            return (new ValidBitmask($enumClass))->passes($attribute, $value);
        });
    }

    private function registerBlueprintMacros(): void
    {
        Blueprint::macro('tinyBitmask', function (string $column): \Illuminate\Database\Schema\ColumnDefinition {
            /** @var Blueprint $this */
            return $this->tinyInteger($column)->unsigned()->default(0);
        });

        Blueprint::macro('smallBitmask', function (string $column): \Illuminate\Database\Schema\ColumnDefinition {
            /** @var Blueprint $this */
            return $this->smallInteger($column)->unsigned()->default(0);
        });

        Blueprint::macro('mediumBitmask', function (string $column): \Illuminate\Database\Schema\ColumnDefinition {
            /** @var Blueprint $this */
            return $this->mediumInteger($column)->unsigned()->default(0);
        });

        Blueprint::macro('bitmask', function (string $column): \Illuminate\Database\Schema\ColumnDefinition {
            /** @var Blueprint $this */
            return $this->unsignedInteger($column)->default(0);
        });
    }
}
