# Clip

[![Tests](https://github.com/vaibhavpandeyvpz/clip/actions/workflows/tests.yml/badge.svg)](https://github.com/vaibhavpandeyvpz/clip/actions/workflows/tests.yml)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-777BB4.svg)](https://www.php.net/)
[![Packagist](https://img.shields.io/packagist/v/vaibhavpandeyvpz/clip.svg)](https://packagist.org/packages/vaibhavpandeyvpz/clip)
[![Packagist Downloads](https://img.shields.io/packagist/dt/vaibhavpandeyvpz/clip.svg)](https://packagist.org/packages/vaibhavpandeyvpz/clip)

**CLI for PHP** - A minimal and simple console application library for PHP with zero required dependencies.

## Features

- 🚀 **Zero dependencies** - Only requires PHP 8.2+
- 📦 **Minimal API** - Simple and intuitive interface
- 🎨 **Colored output** - Built-in support for colored console output
- 💬 **Interactive input** - Ask questions, confirmations, and choices
- 🔧 **Flexible** - Easy to extend and customize

## Installation

```bash
composer require vaibhavpandeyvpz/clip
```

## Quick Start

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Clip\Console;
use Clip\Commands\MyCommand;

$app = new Console([
    MyCommand::class,
]);

exit($app->run());
```

## Creating Commands

Create a command by extending the `Command` class:

```php
<?php

namespace Clip\Commands;

use Clip\Command;
use Clip\Stdio;

class HelloWorld extends Command
{
    public function getName(): string
    {
        return 'hello';
    }

    public function getDescription(): string
    {
        return 'Say hello to the world';
    }

    public function execute(Stdio $stdio): int
    {
        $name = $stdio->getArgument(0, 'World');
        $stdio->writeln("Hello, {$name}!");

        return 0;
    }
}
```

Run the command:

```bash
php console hello
# Output: Hello, World!

php console hello Alice
# Output: Hello, Alice!
```

## Command Line Arguments

### Arguments

Arguments are positional values passed to the command:

```bash
php console command arg1 arg2 arg3
```

Access them in your command:

```php
$stdio->getArgument(0);        // 'arg1'
$stdio->getArgument(1);        // 'arg2'
$stdio->getArguments();         // ['arg1', 'arg2', 'arg3']
```

### Options

Options are key-value pairs or flags:

```bash
php console command --name=John --verbose --force
```

Access them in your command:

```php
$stdio->getOption('name');     // 'John'
$stdio->getOption('verbose');  // true
$stdio->hasOption('force');     // true
$stdio->getOptions();           // ['name' => 'John', 'verbose' => true, 'force' => true]
```

## Output Methods

### Standard Output

```php
$stdio->write('Message');           // Write without newline
$stdio->writeln('Message');         // Write with newline
$stdio->writeln();                  // Write empty line
```

### Colored Output

```php
$stdio->error('Error message');     // Red
$stdio->warning('Warning message'); // Yellow
$stdio->info('Info message');       // Blue
$stdio->debug('Debug message');     // Standard (no color)
$stdio->verbose('Verbose message'); // Standard (no color)
```

Colors are automatically disabled when:

- Output is piped to a file
- `NO_COLOR` environment variable is set
- Terminal doesn't support colors

## Interactive Input

### Ask for Input

```php
$name = $stdio->ask('What is your name?', 'Guest');
// Prompts: What is your name? [Guest]:
// Returns user input or 'Guest' if empty
```

### Confirmations

```php
if ($stdio->confirm('Do you want to continue?', true)) {
    // User confirmed (default: yes)
}
// Prompts: Do you want to continue? [Y/n]:
// Accepts: y, yes, 1, true (case-insensitive)
```

### Choices

```php
$env = $stdio->choice(
    'Select environment:',
    ['development', 'staging', 'production'],
    'development'
);
// Displays numbered list and returns selected choice
```

## Complete Example

```php
<?php

namespace Clip\Commands;

use Clip\Command;
use Clip\Stdio;

class Migrate extends Command
{
    public function getName(): string
    {
        return 'migrate';
    }

    public function getDescription(): string
    {
        return 'Run database migrations';
    }

    public function execute(Stdio $stdio): int
    {
        $connection = $stdio->getOption('connection', 'default');
        $force = $stdio->hasOption('force');

        if ($force) {
            if (!$stdio->confirm('This will overwrite existing data. Continue?', false)) {
                $stdio->warning('Migration cancelled.');
                return 1;
            }
        }

        $stdio->info("Running migrations with connection: {$connection}");

        // Your migration logic here
        $stdio->writeln('Migrations completed successfully!');

        return 0;
    }
}
```

Usage:

```bash
php console migrate --connection=mysql
php console migrate --connection=mysql --force
```

## Requirements

- PHP 8.2 or higher

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## Author

**Vaibhav Pandey**

- Email: contact@vaibhavpandey.com
- GitHub: [@vaibhavpandeyvpz](https://github.com/vaibhavpandeyvpz)
