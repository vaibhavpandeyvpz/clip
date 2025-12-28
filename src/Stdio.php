<?php

declare(strict_types=1);

/*
 * This file is part of vaibhavpandeyvpz/clip package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Clip;

/**
 * Represents standard input/output for console commands.
 *
 * Combines command line input parsing and console output handling
 * in a single class for convenience.
 */
class Stdio
{
    /**
     * The command name.
     */
    protected string $command;

    /**
     * Array of command arguments.
     *
     * @var array<string>
     */
    protected array $arguments = [];

    /**
     * Array of command options (key-value pairs).
     *
     * @var array<string, string|bool>
     */
    protected array $options = [];

    /**
     * Standard output stream.
     */
    protected $stdout;

    /**
     * Standard error stream.
     */
    protected $stderr;

    /**
     * Standard input stream.
     */
    protected $stdin;

    /**
     * Whether colors are supported.
     */
    protected bool $colorsEnabled;

    /**
     * ANSI color codes.
     */
    protected const COLOR_RESET = "\033[0m";

    protected const COLOR_RED = "\033[31m";

    protected const COLOR_YELLOW = "\033[33m";

    protected const COLOR_BLUE = "\033[34m";

    /**
     * Creates a new Stdio instance from command line arguments.
     *
     * @param  array<string>|null  $argv  The command line arguments (typically $_SERVER['argv'])
     * @param  resource|null  $stdout  The standard output stream (defaults to STDOUT)
     * @param  resource|null  $stderr  The standard error stream (defaults to STDERR)
     * @param  resource|null  $stdin  The standard input stream (defaults to STDIN)
     */
    public function __construct(?array $argv = null, $stdout = null, $stderr = null, $stdin = null)
    {
        $this->stdout = $stdout ?? STDOUT;
        $this->stderr = $stderr ?? STDERR;
        $this->stdin = $stdin ?? STDIN;
        $this->colorsEnabled = $this->supportsColors();

        if ($argv === null) {
            $argv = $_SERVER['argv'] ?? [];
        }

        // Remove script name
        array_shift($argv);

        if (empty($argv)) {
            return;
        }

        // First argument is the command name
        $this->command = array_shift($argv);

        // Parse remaining arguments and options
        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                // Option: --key=value or --key
                $option = substr($arg, 2);
                if (str_contains($option, '=')) {
                    [$key, $value] = explode('=', $option, 2);
                    $this->options[$key] = $value;
                } else {
                    $this->options[$option] = true;
                }
            } else {
                // Regular argument
                $this->arguments[] = $arg;
            }
        }
    }

    /**
     * Returns the command name.
     *
     * @return string The command name
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * Returns all arguments.
     *
     * @return array<string> The command arguments
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Returns an argument by index.
     *
     * @param  int  $index  The argument index (0-based)
     * @param  string|null  $default  The default value if argument doesn't exist
     * @return string|null The argument value or default
     */
    public function getArgument(int $index, ?string $default = null): ?string
    {
        return $this->arguments[$index] ?? $default;
    }

    /**
     * Returns all options.
     *
     * @return array<string, string|bool> The command options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns an option value by name.
     *
     * @param  string  $name  The option name
     * @param  string|bool|null  $default  The default value if option doesn't exist
     * @return string|bool|null The option value or default
     */
    public function getOption(string $name, string|bool|null $default = null): string|bool|null
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * Checks if an option exists.
     *
     * @param  string  $name  The option name
     * @return bool True if the option exists, false otherwise
     */
    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    /**
     * Checks if colors are supported for the output stream.
     *
     * @return bool True if colors are supported, false otherwise
     */
    protected function supportsColors(): bool
    {
        // Respect NO_COLOR environment variable (https://no-color.org/)
        if (getenv('NO_COLOR') !== false) {
            return false;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            // Windows: check if ANSI support is available (Windows 10+)
            return getenv('ANSICON') !== false
                || getenv('ConEmuANSI') === 'ON'
                || (function_exists('sapi_windows_vt100_support') && sapi_windows_vt100_support($this->stdout));
        }

        // Unix-like: check if it's a TTY
        if (function_exists('posix_isatty')) {
            return posix_isatty($this->stdout);
        }

        // Fallback: check stream metadata
        $meta = stream_get_meta_data($this->stdout);

        return isset($meta['mode']) && str_contains($meta['mode'], 't');
    }

    /**
     * Applies color to a message if colors are enabled.
     *
     * @param  string  $message  The message to colorize
     * @param  string  $color  The ANSI color code
     * @return string The colorized message
     */
    protected function colorize(string $message, string $color): string
    {
        if (! $this->colorsEnabled) {
            return $message;
        }

        return $color.$message.self::COLOR_RESET;
    }

    /**
     * Writes a message to standard output.
     *
     * @param  string  $message  The message to write
     * @param  bool  $newline  Whether to append a newline
     */
    public function write(string $message, bool $newline = true): void
    {
        fwrite($this->stdout, $message.($newline ? PHP_EOL : ''));
    }

    /**
     * Writes a message to standard error (colored red).
     *
     * @param  string  $message  The message to write
     * @param  bool  $newline  Whether to append a newline
     */
    public function error(string $message, bool $newline = true): void
    {
        $colored = $this->colorize($message, self::COLOR_RED);
        fwrite($this->stderr, $colored.($newline ? PHP_EOL : ''));
    }

    /**
     * Writes a warning message to standard output (colored yellow).
     *
     * @param  string  $message  The message to write
     * @param  bool  $newline  Whether to append a newline
     */
    public function warning(string $message, bool $newline = true): void
    {
        $colored = $this->colorize($message, self::COLOR_YELLOW);
        fwrite($this->stdout, $colored.($newline ? PHP_EOL : ''));
    }

    /**
     * Writes an info message to standard output (colored blue).
     *
     * @param  string  $message  The message to write
     * @param  bool  $newline  Whether to append a newline
     */
    public function info(string $message, bool $newline = true): void
    {
        $colored = $this->colorize($message, self::COLOR_BLUE);
        fwrite($this->stdout, $colored.($newline ? PHP_EOL : ''));
    }

    /**
     * Writes a debug/verbose message to standard output (no color).
     *
     * @param  string  $message  The message to write
     * @param  bool  $newline  Whether to append a newline
     */
    public function debug(string $message, bool $newline = true): void
    {
        $this->write($message, $newline);
    }

    /**
     * Writes a verbose message to standard output (no color).
     *
     * @param  string  $message  The message to write
     * @param  bool  $newline  Whether to append a newline
     */
    public function verbose(string $message, bool $newline = true): void
    {
        $this->write($message, $newline);
    }

    /**
     * Writes a line to standard output.
     *
     * @param  string  $message  The message to write
     */
    public function writeln(string $message = ''): void
    {
        $this->write($message, true);
    }

    /**
     * Asks for user input.
     *
     * @param  string  $question  The question to ask
     * @param  string|null  $default  The default value if user just presses enter
     * @return string The user's input
     */
    public function ask(string $question, ?string $default = null): string
    {
        $prompt = $question;
        if ($default !== null) {
            $prompt .= " [{$default}]";
        }
        $prompt .= ': ';

        $this->write($prompt, false);
        $input = trim((string) fgets($this->stdin));

        return $input !== '' ? $input : ($default ?? '');
    }

    /**
     * Asks for a yes/no confirmation.
     *
     * @param  string  $question  The question to ask
     * @param  bool  $default  The default value if user just presses enter
     * @return bool True if confirmed, false otherwise
     */
    public function confirm(string $question, bool $default = false): bool
    {
        $defaultText = $default ? 'Y/n' : 'y/N';
        $prompt = "{$question} [{$defaultText}]: ";

        $this->write($prompt, false);
        $input = trim((string) fgets($this->stdin));

        if ($input === '') {
            return $default;
        }

        $input = strtolower($input);

        return in_array($input, ['y', 'yes', '1', 'true'], true);
    }

    /**
     * Asks the user to pick from a list of choices.
     *
     * @param  string  $question  The question to ask
     * @param  array<string>  $choices  Array of available choices
     * @param  string|null  $default  The default choice (must be in the choices array)
     * @return string The selected choice
     */
    public function choice(string $question, array $choices, ?string $default = null): string
    {
        if (empty($choices)) {
            throw new \InvalidArgumentException('Choices array cannot be empty.');
        }

        $this->writeln($question);
        $this->writeln('');

        $indexedChoices = [];
        $defaultIndex = null;
        $index = 1;

        foreach ($choices as $choice) {
            $indexedChoices[$index] = $choice;
            if ($default !== null && $choice === $default) {
                $defaultIndex = $index;
            }
            $marker = ($defaultIndex === $index) ? ' (default)' : '';
            $this->writeln("  [{$index}] {$choice}{$marker}");
            $index++;
        }

        $this->writeln('');

        while (true) {
            $prompt = 'Enter your choice';
            if ($defaultIndex !== null) {
                $prompt .= " [{$defaultIndex}]";
            }
            $prompt .= ': ';

            $this->write($prompt, false);
            $input = trim((string) fgets($this->stdin));

            if ($input === '' && $default !== null) {
                return $default;
            }

            if (is_numeric($input)) {
                $selectedIndex = (int) $input;
                if (isset($indexedChoices[$selectedIndex])) {
                    return $indexedChoices[$selectedIndex];
                }
            } else {
                // Allow direct choice value input
                if (in_array($input, $choices, true)) {
                    return $input;
                }
            }

            $this->error('Invalid choice. Please try again.');
        }
    }
}
