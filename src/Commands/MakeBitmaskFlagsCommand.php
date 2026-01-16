<?php

declare(strict_types=1);

namespace Gksh\Transistor\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @return array<string, array<int, string>>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => ['What should the enum be named?', 'E.g. Permission'],
        ];
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->collectFlags();
    }

    public function handle(): ?bool
    {
        if (empty($this->flags)) {
            $this->error('At least one flag is required.');

            return false;
        }

        return parent::handle();
    }

    protected function collectFlags(): void
    {
        $this->info('Enter flag names (empty to finish):');

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

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

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

    private function validateFlagName(string $value): ?string
    {
        $studly = Str::studly($value);

        if (in_array($studly, $this->flags, true)) {
            return "The flag '{$studly}' already exists.";
        }

        return null;
    }
}
