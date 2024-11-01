<?php

$define = new Define(
    workingDir: $argv[1] ?? null
);
$define();

class Define
{
    private string $path;

    private array $composerConfig;

    public function __construct(
        ?string $workingDir = null
    )
    {
        echo "Working dir: $workingDir\n";

        $this->path = __DIR__.'/../../..';

        if (! is_null($workingDir)) {
            $this->path = sprintf('%s/%s', $this->path, $workingDir);
        }

        echo "Path: $this->path\n";

        $this->composerConfig = json_decode(
            file_get_contents($this->path.'/composer.json'),
            true
        );
    }

    public function __invoke(): void
    {
        $this->setOutput('php_version', $this->getPhpVersion());
        $this->setOutput('phpstan_level', (string) $this->getPhpStanLevel());
        $this->setOutput('pint_arguments', $this->getPintArguments());
    }

    private function setOutput(string $name, string $value): void
    {
        exec("echo $name=$value >> \$GITHUB_OUTPUT");
    }

    private function usesPackage(string $package): bool
    {
        $packages = array_merge(
            $this->composerConfig['require'] ?? [],
            $this->composerConfig['require-dev'] ?? []
        );

        return isset($packages[$package]);
    }

    private function getPhpVersion(): string
    {
        if (! isset($this->composerConfig['config']['platform']['php'])) {
            echo "PHP version not set in composer.json\n";
            exit(1);
        }

        return $this->composerConfig['config']['platform']['php'];
    }

    private function getPhpStanLevel(): int
    {
        preg_match(
            pattern: '/level:\s+(\d+)/i',
            subject: file_get_contents($this->path.'/phpstan.neon'),
            matches: $matches
        );

        if (isset($matches[1])) {
            return (int) $matches[1];
        } else {
            echo "PHPStan level not found in phpstan.neon\n";
            exit(1);
        }
    }

    private function getPintArguments(): string
    {
        if ($this->usesPackage('mattsbanner/laravel-core-dev')) {
            return '--config=vendor/mattsbanner/laravel-core-dev/pint.json';
        }

        return '';
    }
}
