<?php

declare(strict_types=1);

namespace Gksh\Transistor\Rules;

use BackedEnum;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use UnitEnum;

use function Gksh\Bitmask\Support\maskValue;

final class ValidBitmask implements ValidationRule
{
    /**
     * @param  class-string<BackedEnum|UnitEnum>|null  $enumClass
     */
    public function __construct(
        private readonly ?string $enumClass = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->passes($attribute, $value)) {
            $fail('The :attribute must be a valid bitmask value.');
        }
    }

    public function passes(string $attribute, mixed $value): bool
    {
        if (! is_int($value) && ! is_numeric($value)) {
            return false;
        }

        $intValue = (int) $value;

        if ($intValue < 0) {
            return false;
        }

        if ($this->enumClass === null) {
            return true;
        }

        if (! enum_exists($this->enumClass)) {
            return false;
        }

        return $intValue <= $this->calculateMaxValue();
    }

    private function calculateMaxValue(): int
    {
        assert($this->enumClass !== null);

        /** @var array<BackedEnum|UnitEnum> $cases */
        $cases = $this->enumClass::cases();

        return array_reduce(
            $cases,
            fn (int $carry, BackedEnum|UnitEnum $case): int => $carry | maskValue($case),
            0,
        );
    }
}
