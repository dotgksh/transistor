<?php

declare(strict_types=1);

namespace Gksh\Transistor\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;

#[AsCommand(name: 'make:bitmask-flags')]
final class MakeBitmaskFlagsCommand extends GeneratorCommand
{
    protected $name = 'make:bitmask-flags';

    protected $description = 'Create a new bitmask flags enum';

    protected $type = 'Enum';

    /** @var list<string> */
    protected array $flags = [];

    protected function getStub(): string
    {
        return __DIR__.'/../stubs/bitmask-flags.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Enums';
    }

    /**
     * @return array<int, array<int, int|string>>
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::OPTIONAL, 'The name of the enum'],
        ];
    }

    protected function getNameInput(): string
    {
        $name = $this->argument('name');

        if (is_string($name) && $name !== '') {
            return trim($name);
        }

        return text(
            label: 'What should the enum be named?',
            placeholder: 'e.g. Permission',
            required: true,
            validate: $this->validateEnumName(...),
        );
    }

    public function handle(): ?bool
    {
        $this->collectFlags();

        if (empty($this->flags)) {
            $this->error('At least one flag is required.');

            return false;
        }

        return parent::handle();
    }

    protected function collectFlags(): void
    {
        info('Enter flag names (empty to finish):');

        for ($index = 1; ; $index++) {
            $flag = text(
                label: "Flag {$index}",
                placeholder: $index === 1 ? 'e.g. Read' : '',
                validate: fn (string $value): ?string => $value === '' ? null : $this->validateFlagName($value),
            );

            if ($flag === '') {
                return;
            }

            $this->flags[] = Str::studly($flag);
        }
    }

    /**
     * @param  string  $stub
     */
    protected function replaceClass($stub, $name): string
    {
        $stub = parent::replaceClass($stub, $name);

        return str_replace('{{ cases }}', $this->buildCases(), $stub);
    }

    protected function buildCases(): string
    {
        $cases = array_map(
            fn (string $flag, int $index): string => "    case {$flag} = 1 << {$index};",
            $this->flags,
            array_keys($this->flags),
        );

        return implode("\n", $cases);
    }

    private function validateEnumName(string $value): ?string
    {
        if (! preg_match('/^[A-Z][a-zA-Z0-9]*$/', $value)) {
            return 'The enum name must be in PascalCase.';
        }

        return null;
    }

    private function validateFlagName(string $value): ?string
    {
        $studly = Str::studly($value);

        if (in_array($studly, $this->flags, true)) {
            return "The flag '{$studly}' already exists.";
        }

        return null;
    }
}
