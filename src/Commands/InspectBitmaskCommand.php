<?php

declare(strict_types=1);

namespace Gksh\Transistor\Commands;

use BackedEnum;
use Illuminate\Console\Command;
use ReflectionEnum;
use Symfony\Component\Console\Attribute\AsCommand;
use UnitEnum;

use function Gksh\Bitmask\Support\maskValue;

#[AsCommand(name: 'bitmask:inspect')]
final class InspectBitmaskCommand extends Command
{
    /** @var string */
    protected $signature = 'bitmask:inspect {enum : The enum class to inspect} {value? : Optional integer value to analyze}';

    /** @var string */
    protected $description = 'Display bitmask flags as a lookup table';

    public function handle(): int
    {
        $enumClass = $this->resolveEnumClass();

        if ($enumClass === null) {
            return self::FAILURE;
        }

        if (! $this->validateEnum($enumClass)) {
            return self::FAILURE;
        }

        $value = $this->resolveValue();

        if ($value === false) {
            return self::FAILURE;
        }

        $this->displayTable($enumClass, $value);

        return self::SUCCESS;
    }

    /**
     * @return class-string<BackedEnum|UnitEnum>|null
     */
    private function resolveEnumClass(): ?string
    {
        /** @var string $input */
        $input = $this->argument('enum');

        // Try the input as-is first (FQCN)
        if (enum_exists($input)) {
            /** @var class-string<BackedEnum|UnitEnum> $input */
            return $input;
        }

        // Try with App\Enums namespace
        $withNamespace = 'App\\Enums\\'.$input;
        if (enum_exists($withNamespace)) {
            /** @var class-string<BackedEnum|UnitEnum> $withNamespace */
            return $withNamespace;
        }

        $this->error("Enum class '{$input}' not found.");

        return null;
    }

    /**
     * @param  class-string<BackedEnum|UnitEnum>  $enumClass
     */
    private function validateEnum(string $enumClass): bool
    {
        $reflection = new ReflectionEnum($enumClass);

        if ($reflection->isBacked()) {
            $backingType = $reflection->getBackingType();

            if (! $backingType instanceof \ReflectionNamedType || $backingType->getName() !== 'int') {
                $this->error('Only integer-backed enums are supported.');

                return false;
            }
        }

        return true;
    }

    private function resolveValue(): int|null|false
    {
        $input = $this->argument('value');

        if ($input === null) {
            return null;
        }

        if (! is_numeric($input) || (int) $input < 0) {
            $this->error('Value must be a non-negative integer.');

            return false;
        }

        return (int) $input;
    }

    /**
     * @param  class-string<BackedEnum|UnitEnum>  $enumClass
     */
    private function displayTable(string $enumClass, ?int $value): void
    {
        /** @var array<BackedEnum|UnitEnum> $cases */
        $cases = $enumClass::cases();

        $caseValues = array_map(maskValue(...), $cases);
        $maxValue = max($value ?? 0, ...$caseValues);
        $bitWidth = max(8, (int) ceil(log($maxValue + 1, 2)));

        $rows = [];

        foreach ($cases as $case) {
            $caseValue = maskValue($case);
            $rows[] = [
                $case->name,
                $caseValue,
                $this->formatBinaryColored($caseValue, $bitWidth),
            ];
        }

        if ($value !== null) {
            $rows[] = new \Symfony\Component\Console\Helper\TableSeparator;
            $rows[] = [
                'Value',
                $value,
                $this->formatBinaryColored($value, $bitWidth),
            ];
        }

        $this->table(['Case', 'Decimal', 'Binary'], $rows);
    }

    private function formatBinaryColored(int $value, int $width): string
    {
        $binary = str_pad(decbin($value), $width, '0', STR_PAD_LEFT);
        $bits = str_split($binary);

        $colored = array_map(
            fn (string $bit): string => $bit === '1'
                ? "<fg=green>{$bit}</>"
                : "<fg=gray>{$bit}</>",
            $bits,
        );

        return implode(' ', $colored);
    }
}
