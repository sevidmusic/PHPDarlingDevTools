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
  _____             __        ___     _  __             _______
 / ___/______ ___ _/ /____   / _ |   / |/ /__ _    __  / ___/ /__ ____ ___
/ /__/ __/ -_) _ `/ __/ -_) / __ |  /    / -_) |/|/ / / /__/ / _ `(_-<(_-<
\___/_/  \__/\_,_/\__/\__/ /_/ |_| /_/|_/\__/|__,__/  \___/_/\_,_/___/___/
', 84);

echo PHP_EOL;

try {
    createNewClassFiles();
} catch (Exception $e) {
    echo PHP_EOL . $e->getMessage() . PHP_EOL;
}

echo newLine();

function highlightText(string $text, int $colorCode): string
{
    return "\033[38;5;0m\033[48;5;" . strval($colorCode) . "m" . $text . "\033[0m";
}

/**
 * Create the new Class's files.
 *
 * @return void
 *
 */
function createNewClassFiles(): void
{
    try {
        outputErrorMessageAndExitIfExpectedArgumentsWereNotSpecified();
        createExpectedDirectories();
        foreach(templatePaths() as $templateName => $templatePath) {
            $appropriatePathForFile = determineAppropriatePathForFile($templateName);
            if(!empty($appropriatePathForFile)) {
            createNewFile(
                $appropriatePathForFile,
                generateSourceCodeFromTemplate($templatePath)
            );
            }
        }
    } catch(Exception $e) {
       /**
         * @todo
         *
         * Clean up anything that was done. If there is an
         * error at any point, everything done successfully
         * must be undone!
         */
        echo PHP_EOL .
            ERROR . 'An error occured: ' .
            PHP_EOL .
            '    ' . $e->getMessage();
    };
}

/**
 * Create the directories that are required for the new Class's
 * files if they do not already exist.
 *
 * @return void
 *
 */
function createExpectedDirectories(): void
{
    createDirectoryIfItDoesNotExist(
        determineAppropriateDirectoryPath('tests', 'interfaces')
    );
    createDirectoryIfItDoesNotExist(
        determineAppropriateDirectoryPath('tests', 'classes')
    );
    createDirectoryIfItDoesNotExist(
        determineAppropriateDirectoryPath('src', 'interfaces')
    );
    createDirectoryIfItDoesNotExist(
        determineAppropriateDirectoryPath('src', 'classes')
    );
}

function createNewFile(string $path, string $content): void
{
    if(file_exists($path)) {
        echo PHP_EOL . WARNING . 'Could not write file because ' .
        'A file already exists at ' . $path . PHP_EOL;
    }
    echo PHP_EOL . 'Writing to ' . $path . PHP_EOL;
    file_put_contents($path, $content);
}


function createDirectoryIfItDoesNotExist(string $path): void
{
    if(
        !is_dir(
           $path
        )
    ) {
        echo PHP_EOL . 'Creating directory at: ' . $path . PHP_EOL;
        mkdir($path, permissions: 0755, recursive: true);
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
        return false;
    }
    return true;
}

function rootDirectoryPath(): string
{
    $specifiedPath = getArgument('path');
    $tmpdirpath = __DIR__ . DIRECTORY_SEPARATOR . 'tmp';
    if(!rootPathIsValid($specifiedPath)) {
        echo PHP_EOL .
            WARNING . 'The specified --path `' . $specifiedPath .
            '` does not exist. The ' . $tmpdirpath . ' will ' .
            'be used as the --path instead';
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


/**
 * Determine an appropriate directory path for a new class file.
 *
 * @return string
 *
 */
function determineAppropriateDirectoryPath(string $testsOrSrc, string $interfaceOrClass): string
{
    $rootDir = rootDirectoryPath();
    $newFileSubDirPath = deriveSubDirectoryPathFromSubnamespace();
    return $rootDir .
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
function determineAppropriatePathForFile(string $templateFileName): string
{
    $rootDir = rootDirectoryPath();
    $newFileSubDirPath = deriveSubDirectoryPathFromSubnamespace();
    return strval(
        match($templateFileName) {
            'TestTrait.php' =>
                determineAppropriateDirectoryPath(
                    'tests',
                    'interfaces'
                ) .
                DIRECTORY_SEPARATOR .
                getArgument('name') . 'TestTrait.php',
            'Test.php' =>
                determineAppropriateDirectoryPath(
                    'tests',
                    'classes'
                ) .
                DIRECTORY_SEPARATOR .
                getArgument('name') . 'Test.php',
            'Interface.php' =>
                determineAppropriateDirectoryPath(
                    'src',
                    'interfaces'
                ) .
                DIRECTORY_SEPARATOR .
                getArgument('name') . '.php',
            'Class.php' =>
                determineAppropriateDirectoryPath(
                    'src',
                    'classes'
                ) .
                DIRECTORY_SEPARATOR .
                getArgument('name') . '.php',
            default => outputErrorMessageAndReturnEmptyStringIfFileCouldNotBeCreated(),
        }
    );
}

/**
 * @return array<string, string> Array of paths to the templates used
 *                               to generate the new Classes files,
 *                               indexed by filename.
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

/**
 * Return the specified argument's value as a string.
 *
 * @return string
 *
 */
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
 * Overview of expected TestTrait Template placeholders:
 *
 * __SUB_NAMESPACE__           The sub namespace to use for the
 *                             new TestTrait.
 *
 * __TARGET_CLASS_NAME__       The name of the interface the TestTrait
 *                             will define tests for.
 *
 * __LC_TARGET_CLASS_NAME__    Lower case form of the name of the
 *                             interface the TestTrait will define
 *                             tests for.
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
