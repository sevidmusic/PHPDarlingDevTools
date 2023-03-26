<?php

define('WARNING', 'Warning: ');
define('ERROR', 'Error: ');
/**
 * This script can generate source code for a new roady Class,
 * including it's Interface, TestTrait, and Test class.
 *
 * The code is generated using the templates located in
 * `devTools/templates`.
 *
 * Usage:
 *
 * ```
 * # Assuming current directory is roady's root directory:
 *
 * # To write:
 * php devTools/GenrateFromTemplate.php \
 *      --name ClassName \
 *      --subnamespace Sub\\Namespace
 *
 * ```
 */
echo PHP_EOL;

try {
    createNewClassFiles();
} catch (Exception $e) {
    echo PHP_EOL . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . PHP_EOL;

/**
 * Create the new Class's files.
 *
 * @return void
 *
 * @throws Exception
 */
function createNewClassFiles(): void
{
    try {
        throwErrorIfExpectedArgumentsWereNotSpecified();
        createExpectedDirectories();
        foreach(templatePaths() as $templateName => $templatePath) {
            createNewFile(
                newFilePath($templateName),
                generateSourceCodeFromTemplate($templatePath)
            );
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
 * Determine the appropriate path to save a file to.
 *
 * @return string
 *
 * @throws Exception
 *
 */
function newFilePath(string $filename): string
{
    $rootDir = rootDirectoryPath();
    $newFileSubDirPath = deriveSubDirectoryPathFromSubnamespace();
    return strval(
        match($filename) {
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
            default => throwErrorIfFileCouldNotBeCreated(),
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
    return strval(getArguments()[$name] ?? '');
}

function throwErrorIfExpectedArgumentsWereNotSpecified(): void
{
    $args = getArguments();

    $example =
            PHP_EOL .
            PHP_EOL .
            'For example:' .
            PHP_EOL .
            PHP_EOL .
            'php NewClass.php \\' .
            PHP_EOL .
            '--path ./path/to/project \\' .
            PHP_EOL .
            '--rootnamespace Foo\\\\Bar \\' .
            PHP_EOL .
            '--name Foo \\' .
            PHP_EOL .
            '--basetestname ProjectNameTest \\' .
            PHP_EOL .
            '--subnamespace Baz\\\\Bazzer' .
            PHP_EOL;
    if(!isset($args['path'])) {
        throw new exception(
            PHP_EOL .
            'You must specify a --path that is the full path to ' .
            'the project the new class will be created for.' .
            $example
        );
    }

    if(!isset($args['name'])) {
        throw new exception(
            PHP_EOL .
            'You must specify a --name for the new Class.' .
            $example
        );
    }

    if(!isset($args['basetestname'])) {
        throw new exception(
            PHP_EOL .
            'You must specify a --basetestname that matches the ' .
            'name of the projects base test class. This class ' .
            'should exist at `tests/BASETESTNAMETest.php' .
            PHP_EOL .
            PHP_EOL .
            'Note: This script is intended for use creating Darling '.
            'libraries, if your project is not a darling library ' .
            'then this parameter will probably not make sense to ' .
            'to you, and you are probably using this script for ' .
            'for the wrong purpose.' .
            $example
        );
    }

    if(!isset($args['rootnamespace'])) {
        throw new exception(
            PHP_EOL .
            'You must specify a --rootnamespace. This will be ' .
            'the part of the namespace that should precede the ' .
            '--subnamespace.' .
            PHP_EOL .
            PHP_EOL .
            'For example: ' .
            'If the --subnamespace is `Sub\\Namespace` and the ' .
            ' --rootnamespace is `Root\\Namespace`' .
            'then the complete namespace would ' .
            'would be Root\\Namespace\\classes\\Sub\\Namespace`' .
            PHP_EOL .
            'Another example:' .
            PHP_EOL .
            str_replace('For example:', '', $example)
        );
    }

    if(!isset($args['subnamespace'])) {
        throw new exception(
            PHP_EOL .
            'You must specify a --subnamespace. This will be ' .
            'the part of the namespace that should follow the ' .
            'projects root namespace.' .
            PHP_EOL .
            PHP_EOL .
            'For example: ' .
            'If the projects root namespace is ' .
            '`Root\\Namespace` and the subnamespace ' .
            'is `Sub\\Namespace` then the complete namespace would ' .
            'would be Root\\Namespace\\classes\\Sub\\Namespace`' .
            PHP_EOL .
            'Another example:' .
            PHP_EOL .
            str_replace('For example:', '', $example)
        );
    }
}

function throwErrorIfFileCouldNotBeCreated(): void
{
    throw new exception(
        'You must specify a --name and --subnamespace.'
    );
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
