<?php

/*
 * This script can be used to setup a new class for development in a
 * Darling PHP library or project.
 *
 * This script will generate the required boilerplate for a new
 * Darling class, including it's Interface, TestTrait, and Test class.
 *
 * The source code for the Class, Interface, TestTrait, and Test class
 * is generated using the templates located in this library's
 * `./templates` directory.
 *
 * Usage:
 *
 * Assuming the Darling Dev Tools library has been installed, and
 * the current directory is the root directory of the project the
 * new class should be created for:
 *
 * ```
 * php ./vendor/darling/php-darling-dev-tools/NewClass.php \
 * --path ./ \
 * --rootnamespace Darling\\ProjectName \
 * --name NewClassName \
 * --basetestname NameOfBaseTestClassForProject \
 * --subnamespace sub\\namespace
 *
 * ```
 */

declare(strict_types=1);

define('WARNING', 'Warning: ');
define('ERROR', 'Error: ');

echo PHP_EOL;
echo highlightText(
    '
|      _  __             _______             |
|     / |/ /__ _    __  / ___/ /__ ____ ___  |
|    /    / -_) |/|/ / / /__/ / _ `(_-<(_-<  |
|   /_/|_/\__/|__,__/  \___/_/\_,_/___/___/  |
',
    random_int(20, 229)
);

echo PHP_EOL;

createNewClassFiles(rootDirectoryPath());

echo newLine();

function createNewClassFiles(string $rootDirectoryPath): void
{
    outputErrorMessageAndExitIfExpectedArgumentsWereNotSpecified();
    createExpectedDirectories($rootDirectoryPath);
    foreach (templatePaths() as $templateName => $templatePath) {
        $appropriatePathForFile = determinePathToSaveFileTo(
            $templateName,
            $rootDirectoryPath
        );
        if (!empty($appropriatePathForFile)) {
            if (!is_readable($templatePath)) {
                outputMessage(highlightText("Error: Template not found at {$templatePath}", 196));

                continue;
            }

            createNewFile(
                $appropriatePathForFile,
                generateSourceCodeFromTemplate($templatePath)
            );
        }
    }
}

function createExpectedDirectories(string $rootDirectoryPath): void
{
    $dirs = [
        ['tests', 'interfaces'],
        ['tests', 'classes'],
        ['src', 'interfaces'],
        ['src', 'classes'],
    ];

    foreach ($dirs as $dir) {
        createDirectoryIfItDoesNotExist(
            constructAppropriateDirectoryPath($dir[0], $dir[1], $rootDirectoryPath)
        );
    }
}

function generateSourceCodeFromTemplate(string $templatePath): string
{
    $template = strval(file_get_contents($templatePath));
    $replacements = [
        '__BASE_TEST_NAME__' => getArgument('basetestname'),
        '__ROOT_NAMESPACE__' => getArgument('rootnamespace'),
        '__TARGET_CLASS_NAME__' => getArgument('name'),
        '__SUB_NAMESPACE__' => getArgument('subnamespace'),
        '__LC_TARGET_CLASS_NAME__' => lcfirst(getArgument('name')),
    ];

    return str_replace(
        array_keys($replacements),
        array_values($replacements),
        $template
    );
}

function joinPaths(string ...$parts): string
{
    return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $parts));
}

function createDirectoryIfItDoesNotExist(string $path): void
{
    if (!is_dir($path)) {
        $output = highlightText('Creating directory: ' . $path . ' ', 66);
        if (mkdir($path, 0o755, true)) {
            $output .= successIndicator();
        } else {
            $output .= errorIndicator() . PHP_EOL . highlightText('Failed to create: ' . $path, 208);
        }
        outputMessage($output);
    }
}

function createNewFile(string $path, string $content): void
{
    if (file_exists($path)) {
        outputMessage(
            highlightText('Skipping ', 174)
            . highlightText($path, 202)
            . highlightText(' (Already exists)', 174)
        );

        return;
    }

    $output = highlightText('Writing ' . $path, 66);
    if (false !== file_put_contents($path, $content)) {
        $output .= successIndicator();
    } else {
        $output .= errorIndicator() . highlightText(' Failed to write: ' . $path, 208);
    }
    outputMessage($output);
}

function determinePathToSaveFileTo(string $templateFileName, string $rootDirectoryPath): string
{
    $name = getArgument('name');

    return match ($templateFileName) {
        'TestTrait.php' => joinPaths(
            constructAppropriateDirectoryPath('tests', 'interfaces', $rootDirectoryPath),
            $name . 'TestTrait.php'
        ),
        'Test.php' => joinPaths(
            constructAppropriateDirectoryPath('tests', 'classes', $rootDirectoryPath),
            $name . 'Test.php'
        ),
        'Interface.php' => joinPaths(
            constructAppropriateDirectoryPath('src', 'interfaces', $rootDirectoryPath),
            $name . '.php'
        ),
        'Class.php' => joinPaths(
            constructAppropriateDirectoryPath('src', 'classes', $rootDirectoryPath),
            $name . '.php'
        ),
        default => '',
    };
}

function constructAppropriateDirectoryPath(string $type, string $category, string $root): string
{
    $subPath = str_replace('\\', DIRECTORY_SEPARATOR, getArgument('subnamespace'));

    return joinPaths($root, $type, $category, $subPath);
}

function rootDirectoryPath(): string
{
    $specifiedPath = getArgument('path');
    if (!is_dir($specifiedPath)) {
        $tmp = joinPaths(__DIR__, 'tmp');
        outputMessage(
            highlightText(
                WARNING
                . 'The specified --path, ' . $specifiedPath
                . ' , does not exist.',
                196
            )
        );

        outputMessage(
            highlightText(
                "The following --path will be used instead: {$tmp}",
                196
            )
        );

        return $tmp;
    }

    return $specifiedPath;
}

/** @return array<string, string> */
function templatePaths(): array
{
    $base = joinPaths(__DIR__, 'templates');

    return [
        'TestTrait.php' => joinPaths($base, 'TestTrait.php'),
        'Test.php' => joinPaths($base, 'Test.php'),
        'Interface.php' => joinPaths($base, 'Interface.php'),
        'Class.php' => joinPaths($base, 'Class.php'),
    ];
}

/** @return array<mixed> */
function getArguments(): array
{
    $args = getopt('', ['path:', 'rootnamespace:', 'name:', 'subnamespace:', 'basetestname:']);

    return is_array($args) ? $args : [];
}

function getArgument(string $name): string
{
    $args = getArguments();

    return (isset($args[$name]) && is_string($args[$name])) ? $args[$name] : '';
}

function highlightText(string $text, int $colorCode): string
{
    return "\033[38;5;0m\033[48;5;" . $colorCode . 'm' . $text . "\033[0m";
}

function successIndicator(): string
{
    return highlightText(' ✔ ', 83);
}
function errorIndicator(): string
{
    return highlightText(' X ', 196);
}
function newLine(): string
{
    return PHP_EOL . PHP_EOL;
}

function outputMessage(string $message): void
{
    echo PHP_EOL . $message . PHP_EOL;
}

function outputMessageAndExit(string $message, int $exitCode = 1): void
{
    outputMessage($message);

    exit($exitCode);
}

function outputErrorMessageAndExitIfExpectedArgumentsWereNotSpecified(): void
{
    $required = ['name', 'path', 'rootnamespace', 'subnamespace', 'basetestname'];
    $args = getArguments();

    foreach ($required as $field) {
        if (!isset($args[$field])) {
            $example = newLine() . 'For example:' . newLine() . 'php NewClass.php \\' . exampleArgs($field);
            outputMessageAndExit(
                PHP_EOL
                . 'Missing required argument: '
                . highlightText("--{$field}", 202)
                . $example
            );
        }
    }
}

function exampleArgs(string $highlightArg = ''): string
{
    $name = '--name Foo \\';
    $path = '--path ./path/to/project \\';
    $rootnamespace = '--rootnamespace Foo\\\Bar \\';
    $subnamespace = '--subnamespace Baz\\\Bazzer \\';
    $basetestname = '--basetestname ProjectNameTest';

    return PHP_EOL
        . (
            'name' === $highlightArg
            ? highlightText($name, 202)
            : $name
        )
    . PHP_EOL
        . (
            'path' === $highlightArg
            ? highlightText($path, 202)
            : $path
        )
    . PHP_EOL
        . (
            'rootnamespace' === $highlightArg
            ? highlightText($rootnamespace, 202)
            : $rootnamespace
        )
    . PHP_EOL
        . (
            'subnamespace' === $highlightArg
            ? highlightText($subnamespace, 202)
            : $subnamespace
        )
    . PHP_EOL
        . (
            'basetestname' === $highlightArg
            ? highlightText($basetestname, 202)
            : $basetestname
        )
    . PHP_EOL;
}
