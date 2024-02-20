<?php

const BASE_DIR = __DIR__.'/../..';

setOutput('php_version', getPhpVersion());
setOutput('phpstan_level', (string) getPhpStanLevel());

function setOutput(string $name, string $value): void
{
    exec("echo $name=$value >> \$GITHUB_OUTPUT");
}

function getPhpVersion(): string
{
    $composer = json_decode(
        file_get_contents(BASE_DIR.'/composer.json'),
        true
    );

    if (! isset($composer['config']['platform']['php'])) {
        echo "PHP version not set in composer.json\n";
        exit(1);
    }

    return $composer['config']['platform']['php'];
}

function getPhpStanLevel(): int
{
    preg_match(
        pattern: '/level:\s+(\d+)/i',
        subject: file_get_contents(BASE_DIR.'/phpstan.neon'),
        matches: $matches
    );

    if (isset($matches[1])) {
        return (int) $matches[1];
    } else {
        echo "PHPStan level not found in phpstan.neon\n";
        exit(1);
    }
}