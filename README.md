```
   ___           ___           ___          ______          __
  / _ \___ _____/ (_)__  ___ _/ _ \___ _  _/_  __/__  ___  / /__
 / // / _ `/ __/ / / _ \/ _ `/ // / -_) |/ // / / _ \/ _ \/ (_-<
/____/\_,_/_/ /_/_/_//_/\_, /____/\__/|___//_/  \___/\___/_/___/
                       /___/

```

Development tools for development of Darling PHP libraries and
projects.

This library provides a script named `NewClass.php` which can be used
to setup a new class for development in a Darling PHP library or
project.

The `NewClass.php` script will generate the required boilerplate
for a new Darling class, including it's Interface, TestTrait, and
Test class.

The source code for the Class, Interface, TestTrait, and Test
class is generated using the templates located in this library's
`./templates` directory.

# Installation:

```
composer require darling/php-darling-dev-tools

```

# Usage:

Assuming the Darling Dev Tools library has been installed, and the
current directory is the root directory of the project to create a
new class for:

```
php ./vendor/darling/php-darling-dev-tools/NewClass.php \
--path ./ \
--rootnamespace Darling\\ProjectName \
--name NewClassName \
--basetestname NameOfBaseTestClassForProject \
--subnamespace sub\\namespace

```

### Arguments

```
--path
  The path to the project the class will be created for.
  For example: ./path/to/project

--rootnamespace
  The rootnamespace of the project.
  For example: Darling\\ProjectName

--name
  The name of the new class.
  For example: NewClassName \

--basetestname
  The name of the projects base PHPUnit test class.
  For example: ProjectNameTest \

--subnamespace
  The subnamespace to define for the class.
  This will follow the --rootnamespace.
  For example: classes\\utilities

```
