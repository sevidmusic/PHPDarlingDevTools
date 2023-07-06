<?php

declare(strict_types=1);

define('WARNING', 'Warning: ');
define('ERROR', 'Error: ');

/**
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

echo PHP_EOL;
echo highlightText(
'
|     _  __             _______             |
|    / |/ /__ _    __  / ___/ /__ ____ ___  |
|   /    / -_) |/|/ / / /__/ / _ `(_-<(_-<  |
|  /_/|_/\__/|__,__/  \___/_/\_,_/___/___/  |
', rand(20, 229));

echo PHP_EOL;

createNewClassFiles(rootDirectoryPath());

echo newLine();

function highlightText(string $text, int $colorCode): string
{
    return "\033[38;5;0m\033[48;5;" . strval($colorCode) . "m" . $text . "\033[0m";
}

function createNewClassFiles(string $rootDirectoryPath): void
{
        outputErrorMessageAndExitIfExpectedArgumentsWereNotSpecified();
        createExpectedDirectories($rootDirectoryPath);
        foreach(templatePaths() as $templateName => $templatePath) {
            $appropriatePathForFile = determinePathToSaveFileTo(
                $templateName,
                $rootDirectoryPath
            );
            if(!empty($appropriatePathForFile)) {
                createNewFile(
                    $appropriatePathForFile,
                    generateSourceCodeFromTemplate($templatePath)
                );
            }
        }
}

function createExpectedDirectories(string $rootDirectoryPath): void
{
    createDirectoryIfItDoesNotExist(
        constructAppropriateDirectoryPath('tests', 'interfaces', $rootDirectoryPath)
    );
    createDirectoryIfItDoesNotExist(
        constructAppropriateDirectoryPath('tests', 'classes', $rootDirectoryPath)
    );
    createDirectoryIfItDoesNotExist(
        constructAppropriateDirectoryPath('src', 'interfaces', $rootDirectoryPath)
    );
    createDirectoryIfItDoesNotExist(
        constructAppropriateDirectoryPath('src', 'classes', $rootDirectoryPath)
    );
}

function createNewFile(string $path, string $content): void
{
    if(file_exists($path)) {
        outputMessage(
            highlightText(
                'Skipping ',
                174
            ) .
            highlightText(
                $path,
                202
            ) .
            highlightText(
                ' because it already exists',
                174
            )

        );
    } else {
        $output = highlightText('writing ' . $path, 66);
        if(file_put_contents($path, $content) > 0) {
            $output .= successIndicator();
        } else {
            $output .= errorIndicator();
            $output .= highlightText('Failed to write: ' . $path, 208);
        }
        outputMessage($output);
    }
}

function successIndicator(): string
{
    return highlightText(' ✔ ', 83);
}

function errorIndicator(): string
{
    return highlightText(' X ', 196);
}

function createDirectoryIfItDoesNotExist(string $path): void
{
    if(!is_dir($path)) {
        $output = highlightText('Creating new directory at ' . $path . ' ', 66);
        if(mkdir($path, permissions: 0755, recursive: true) !== false) {
            $output .= successIndicator();
        } else {
            $output .= errorIndicator() . PHP_EOL;
            $output .= highlightText('Failed to create directory: ' . $path . ' ', 208);
        }
        outputMessage($output);
    }
}

function rootPathIsValid(string $path): bool
{

    if(
        empty($path)
        ||
        $path === DIRECTORY_SEPARATOR
        ||
        $path === '/'
        ||
        $path === '/home'
        ||
        !is_dir($path)
    ) {
        outputMessage(
            highlightText(
                WARNING .
                'The specified --path `',
                196
            ) .
            highlightText(
                $path,
                202
            ) .
            highlightText(
                '` cannot be used. ',
                196
            ) .
            highlightText(
                tmpDirPath(),
                202
            ) .
            highlightText(
                ' will ' .
                'be used as the --path instead',
                196
            ) . newLine()
        );
        return false;
    }
    return true;
}

function tmpDirPath(): string
{
    return __DIR__ . DIRECTORY_SEPARATOR . 'tmp';
}

function rootDirectoryPath(): string
{
    $specifiedPath = getArgument('path');
    $tmpdirpath = tmpDirPath();
    if(!rootPathIsValid($specifiedPath)) {
        return $tmpdirpath;
    }
    return $specifiedPath;
}

function deriveSubDirectoryPathFromSubnamespace(): string
{
    return str_replace(
        '\\',
        '/',
        getArgument('subnamespace')
    );
}

function constructAppropriateDirectoryPath(string $testsOrSrc, string $interfaceOrClass, string $rootDirectoryPath): string
{
    $newFileSubDirPath = deriveSubDirectoryPathFromSubnamespace();
    return $rootDirectoryPath .
        DIRECTORY_SEPARATOR .
        $testsOrSrc .
        DIRECTORY_SEPARATOR .
        $interfaceOrClass .
        DIRECTORY_SEPARATOR .
        $newFileSubDirPath;
}

/**
 * Determine the appropriate path to save a file to based
 * on the provided $templateFileName name.
 *
 * If the $templateFileName does not match an expected
 * template file name an empty string will be returned.
 *
 * @return string
 *
 */
function determinePathToSaveFileTo(string $templateFileName, string $rootDirectoryPath): string
{
    $newFileSubDirPath = deriveSubDirectoryPathFromSubnamespace();
    return strval(
        match($templateFileName) {
            'TestTrait.php' =>
                constructAppropriateDirectoryPath(
                    'tests',
                    'interfaces',
                    $rootDirectoryPath
                ) .
                DIRECTORY_SEPARATOR .
                getArgument('name') . 'TestTrait.php',
            'Test.php' =>
                constructAppropriateDirectoryPath(
                    'tests',
                    'classes',
                    $rootDirectoryPath
                ) .
                DIRECTORY_SEPARATOR .
                getArgument('name') . 'Test.php',
            'Interface.php' =>
                constructAppropriateDirectoryPath(
                    'src',
                    'interfaces',
                    $rootDirectoryPath
                ) .
                DIRECTORY_SEPARATOR .
                getArgument('name') . '.php',
            'Class.php' =>
                constructAppropriateDirectoryPath(
                    'src',
                    'classes',
                    $rootDirectoryPath
                ) .
                DIRECTORY_SEPARATOR .
                getArgument('name') . '.php',
            default => outputErrorMessageAndReturnEmptyStringIfFileCouldNotBeCreated(),
        }
    );
}

/**
 * Return an array of paths to the templates used to generate the new
 * Class's files. The array will be indexed by filename.
 *
 * @return array<string, string>
 *
 */
function templatePaths(): array
{
    return [
        'TestTrait.php' => strval(
            realpath(
                __DIR__ .
                DIRECTORY_SEPARATOR .
                'templates' .
                DIRECTORY_SEPARATOR .
                'TestTrait.php'
            )
        ),
        'Test.php' => strval(
            realpath(
                __DIR__ .
                DIRECTORY_SEPARATOR .
                'templates' .
                DIRECTORY_SEPARATOR .
                'Test.php'
            )
        ),
        'Interface.php' => strval(
            realpath(
                __DIR__ .
                DIRECTORY_SEPARATOR .
                'templates' .
                DIRECTORY_SEPARATOR .
                'Interface.php'
            )
        ),
        'Class.php' => strval(
            realpath(
                __DIR__ .
                DIRECTORY_SEPARATOR .
                'templates' .
                DIRECTORY_SEPARATOR .
                'Class.php'
            )
        ),
    ];
}

/**
 * Return the supplied arguments in an array.
 *
 * @return array<string, array<int, mixed>|string|false> Array: ['name' => 'value', 'subnamespace' => 'value']
 *
 */
function getArguments(): array
{
    $args = getopt('', ['path:', 'rootnamespace:', 'name:', 'subnamespace:', 'basetestname:']);
    return (is_array($args) ? $args : []);
}

function getArgument(string $name): string
{
    $arguments = getArguments();
    return match(isset($arguments[$name]) && is_string($arguments[$name])) {
        true => $arguments[$name],
        default => '',
    };
}

function exampleArgs(string $highlightArg = '') : string
{
    return PHP_EOL .
    ($highlightArg === 'name' ? highlightText('--name Foo \\', 202) : '--name Foo \\') .
    PHP_EOL .
    ($highlightArg === 'path' ? highlightText('--path ./path/to/project \\', 202) : '--path ./path/to/project \\') .
    PHP_EOL .
    ($highlightArg === 'rootnamespace' ? highlightText('--rootnamespace Foo\\\\Bar \\', 202) : '--rootnamespace Foo\\\\Bar \\') .
    PHP_EOL .
    ($highlightArg === 'subnamespace' ? highlightText('--subnamespace Baz\\\\Bazzer \\', 202) : '--subnamespace Baz\\\\Bazzer \\') .
    PHP_EOL .
    ($highlightArg === 'basetestname' ? highlightText('--basetestname ProjectNameTest', 202) : '--basetestname ProjectNameTest') .
    PHP_EOL;
}

function newLine(): string
{
    return str_repeat(PHP_EOL, 2);
}

function outputErrorMessageAndExitIfExpectedArgumentsWereNotSpecified(): void
{
    $args = getArguments();
    $example = newLine() . 'For example:' . newLine() . 'php NewClass.php \\';
    if(!isset($args['name'])) {
        outputMessageAndExit(
            PHP_EOL .
            'You must specify a ' . highlightText('--name', 202) . ' for the new Class.' .
            $example. exampleArgs('name')
        );
    }

    if(!isset($args['path'])) {
        outputMessageAndExit(
            PHP_EOL .
            'You must specify a ' . highlightText('--path', 202) . ' that is the full path to ' .
            'the project the new class will be created for.' .
            $example . exampleArgs('path')
        );
    }

    if(!isset($args['rootnamespace'])) {
        outputMessageAndExit(
            PHP_EOL .
            'You must specify a ' .
            highlightText('--rootnamespace', 202) .
            '. This will be ' .
            'the part of the namespace that should precede the ' .
            '--subnamespace.' .
            newLine() .
            'For example: ' .
            'If the --subnamespace is `Sub\\Namespace` and the ' .
            ' --rootnamespace is `Root\\Namespace`' .
            'then the complete namespace would ' .
            'would be Root\\Namespace\\classes\\Sub\\Namespace`' .
            PHP_EOL .
            'Another example:' .
            PHP_EOL .
            str_replace('For example:', '', $example . exampleArgs('rootnamespace'))
        );
    }

    if(!isset($args['subnamespace'])) {
        outputMessageAndExit(
            PHP_EOL .
            'You must specify a ' .
            highlightText('--subnamespace', 202) .
            '. This will be ' .
            'the part of the namespace that should follow the ' .
            'projects root namespace.' .
            newLine() .
            'For example: ' .
            'If the projects root namespace is ' .
            '`Root\\Namespace` and the subnamespace ' .
            'is `Sub\\Namespace` then the complete namespace would ' .
            'would be Root\\Namespace\\classes\\Sub\\Namespace`' .
            PHP_EOL .
            'Another example:' .
            PHP_EOL .
            str_replace('For example:', '', $example . exampleArgs('subnamespace'))
        );
    }

    if(!isset($args['basetestname'])) {
        outputMessageAndExit(
            PHP_EOL .
            'You must specify a ' .
            highlightText('--basetestname', 202) .
            ' that matches the ' .
            'name of the projects base test class. This class ' .
            'should exist at `tests/BASETESTNAMETest.php' .
            newLine() .
            'Note: This script is intended for use creating Darling '.
            'libraries, if your project is not a darling library ' .
            'then this parameter will probably not make sense to ' .
            'to you, and you are probably using this script for ' .
            'for the wrong purpose.' .
            $example. exampleArgs('basetestname')
        );
    }

}

function outputMessage(string $message) : void
{
    echo PHP_EOL . $message . PHP_EOL;
}

function outputMessageAndExit(string $message, int $exitCode = 1): void
{
    outputMessage($message);
    exit($exitCode);
}

function outputErrorMessageAndReturnEmptyStringIfFileCouldNotBeCreated(): string
{
    outputMessage(
        'You must specify a --name and --subnamespace.'
    );
    return '';
}

/**
 * Generate the appropriate source code using the template located
 * at the specified $templatePath.
 *
 * The templates use placeholders to indicate what actual source
 * code should be generated and where.
 *
 * Overview of expected Template placeholders:
 *
 * __BASE_TEST_NAME__          The name of the base test defined
 *                             by the project. This test name will
 *                             correspond to the name of the test
 *                             defined at:
 *
 *                             tests/BASETESTNAME.php
 *
 * __ROOT_NAMESPACE__          The root namespace to use.
 *
 * __TARGET_CLASS_NAME__       The name to assign to the class.
 *
 * __SUB_NAMESPACE__           The sub namespace to use.
 *
 * __LC_TARGET_CLASS_NAME__    Lower case form of the name of the
 *                             class to generate source code for.
 *
 */
function generateSourceCodeFromTemplate(string $templatePath): string
{
    $template = strval(file_get_contents($templatePath));
    return str_replace(
        [
            '__BASE_TEST_NAME__',
            '__ROOT_NAMESPACE__',
            '__TARGET_CLASS_NAME__',
            '__SUB_NAMESPACE__',
            '__LC_TARGET_CLASS_NAME__',
        ],
        [
            getArgument('basetestname'),
            getArgument('rootnamespace'),
            getArgument('name'),
            getArgument('subnamespace'),
            lcfirst(getArgument('name'))
        ],
        $template
    );
}
