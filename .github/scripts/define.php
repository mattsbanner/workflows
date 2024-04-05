<?php

$define = new Define(
    workingDir: $argv[1] ?? null
);
$define();

class Define
{
    private string $path;

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
    }

    public function __invoke(): void
    {
        $this->setOutput('php_version', $this->getPhpVersion());
        $this->setOutput('phpstan_level', (string) $this->getPhpStanLevel());
    }

    private function setOutput(string $name, string $value): void
    {
        exec("echo $name=$value >> \$GITHUB_OUTPUT");
    }

    private function getPhpVersion(): string
    {
        $composer = json_decode(
            file_get_contents($this->path.'/composer.json'),
            true
        );

        if (! isset($composer['config']['platform']['php'])) {
            echo "PHP version not set in composer.json\n";
            exit(1);
        }

        return $composer['config']['platform']['php'];
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
}
