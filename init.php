#!/usr/bin/env php
<?php declare(strict_types=1);

/*
 |--------------------------------------------------------------------------
 | Init project script
 |--------------------------------------------------------------------------
 |
 | It replace name and description along relevant files.
 |
 | Inspired by Spatie.
 | @see https://github.com/spatie/package-skeleton-laravel
 |
 */

/**
 * @param string $question
 * @param string $default
 *
 * @return string
 */
function ask(string $question, string $default = ''): string
{
    /** @var string $answer */
    $answer = readline($question . ($default ? " ({$default})" : null) . ': ');

    if (!$answer) {
        return $default;
    }

    return $answer;
}

/**
 * @param string $question
 * @param bool $default
 *
 * @return bool
 */
function confirm(string $question, bool $default = false): bool
{
    /** @var string $answer */
    $answer = ask($question . ' (' . ($default ? 'Y/n' : 'y/N') . ')');

    if (!$answer) {
        return $default;
    }

    return mb_strtolower($answer) === 'y';
}

/**
 * @param string $line
 *
 * @return void
 */
function writeln(string $line): void
{
    echo $line . \PHP_EOL;
}

/**
 * @param string $command
 *
 * @return string
 */
function run(string $command): string
{
    /** @var string|false|null $return */
    $return = shell_exec($command);

    if (!$return) {
        return '';
    }

    return trim($return);
}

/**
 * @param string $subject
 *
 * @return string
 */
function slugify(string $subject): string
{
    return mb_strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $subject), '-'));
}

/**
 * @param string $subject
 *
 * @return string
 */
function humanize(string $subject): string
{
    return ucwords(trim(str_replace('-', ' ', $subject)));
}

/**
 * @param string $file
 * @param array $replacements
 *
 * @return void
 */
function replace_in_file(string $file, array $replacements): void
{
    /** @var string $contents */
    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        str_replace(
            array_keys($replacements),
            array_values($replacements),
            $contents
        )
    );
}

/**
 * @param string $file
 *
 * @return void
 */
function remove_readme_paragraphs(string $file): void
{
    /** @var string $contents */
    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        preg_replace('/<!--delete-->.*<!--\/delete-->/s', '', $contents) ?: $contents
    );
}

/**
 * @return array
 */
function replaceForWindows(): array
{
    return preg_split('/\\r\\n|\\r|\\n/', run('dir /S /B * | findstr /v /i .git\ | findstr /v /i vendor | findstr /v /i ' . basename(__FILE__) . ' | findstr /r /i /M /F:/ "laravel-template laravel-template-name laravel-template-description"'));
}

/**
 * @return string[]
 */
function replaceForAllOtherOSes(): array
{
    return explode(\PHP_EOL, run('grep -E -r -l -i "laravel-template|laravel-template-name|laravel-template-description" --exclude-dir=vendor ./* .env.example ./.ddev/* | grep -v ' . basename(__FILE__)));
}

/** @var string|false $currentDirectory */
$currentDirectory = getcwd();

/** @var string $folderName */
$folderName = basename($currentDirectory);

/** @var string $projectName */
$projectName = humanize(ask('Project name', $folderName));

/** @var string $projectSlug */
$projectSlug = slugify($projectName);

/** @var string $description */
$description = ask('Project description', "Here we are with {$projectName}");

writeln('------');
writeln("Project name           : {$projectName}");
writeln("Project slug           : {$projectSlug}");
writeln("Project description    : {$description}");
writeln('------');

writeln('This script will replace the above values in all relevant files in the project directory.');

if (!confirm('Modify files?', true)) {
    exit(1);
}

/** @var string[] $files */
$files = (str_starts_with(mb_strtoupper(\PHP_OS), 'WIN') ? replaceForWindows() : replaceForAllOtherOSes());

/** @var string $file */
foreach ($files as $file) {
    replace_in_file(
        $file,
        [
            'laravel-template-description' => $description,
            'laravel-template-name' => $projectName,
            'laravel-template' => $projectSlug,
        ]
    );

    match (true) {
        str_contains($file, 'README.md') => remove_readme_paragraphs($file),
        default => [],
    };
}

copy('.env.example', '.env');

run('php artisan key:generate');

confirm('Let this script delete itself?', true) && unlink(__FILE__);
