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
 * Main console application class for managing and running console commands.
 *
 * Provides a simple way to register commands and execute them
 * based on command line input.
 */
class Console
{
    /**
     * Array of command class names or instances.
     *
     * @var array<string|Command>
     */
    protected array $commands = [];

    /**
     * Creates a new Console instance.
     *
     * @param  array<string|Command>  $commands  Array of command class names or instances
     */
    public function __construct(array $commands = [])
    {
        foreach ($commands as $command) {
            $this->command($command);
        }
    }

    /**
     * Adds a command to the console.
     *
     * @param  string|Command  $command  Command class name or instance
     * @return $this
     */
    public function command(string|Command $command): self
    {
        $this->commands[] = $command;

        return $this;
    }

    /**
     * Resolves a command from a class name or returns the instance.
     *
     * @param  string|Command  $command  Command class name or instance
     * @return Command The command instance
     */
    protected function resolveCommand(string|Command $command): Command
    {
        if ($command instanceof Command) {
            $instance = $command;
        } else {
            if (! class_exists($command)) {
                throw new \RuntimeException("Command class '{$command}' not found.");
            }

            $instance = new $command;

            if (! $instance instanceof Command) {
                throw new \RuntimeException("Command class '{$command}' must extend Command.");
            }
        }

        return $instance;
    }

    /**
     * Gets a command by name.
     *
     * @param  string  $name  The command name
     * @return Command|null The command instance or null if not found
     */
    protected function getCommand(string $name): ?Command
    {
        foreach ($this->commands as $command) {
            $instance = $this->resolveCommand($command);
            if ($instance->getName() === $name) {
                return $instance;
            }
        }

        return null;
    }

    /**
     * Lists all available commands.
     *
     * @param  Stdio  $stdio  The stdio for writing messages
     */
    protected function listCommands(Stdio $stdio): void
    {
        if (empty($this->commands)) {
            $stdio->writeln('No commands available.');

            return;
        }

        $stdio->writeln('Available commands:');
        $stdio->writeln('');

        foreach ($this->commands as $command) {
            $instance = $this->resolveCommand($command);
            $name = $instance->getName();
            $description = $instance->getDescription();
            $stdio->writeln("  {$name}\t{$description}");
        }
    }

    /**
     * Runs the console application.
     *
     * Parses command line input and executes the appropriate command.
     *
     * @param  array<string>|null  $argv  Command line arguments (defaults to $_SERVER['argv'])
     * @return int The exit code
     */
    public function run(?array $argv = null): int
    {
        $stdio = new Stdio($argv);

        $commandName = $stdio->getCommand();

        if (empty($commandName)) {
            $this->listCommands($stdio);

            return 0;
        }

        $command = $this->getCommand($commandName);

        if ($command === null) {
            $stdio->error("Command '{$commandName}' not found.");
            $stdio->writeln('');
            $this->listCommands($stdio);

            return 1;
        }

        try {
            return $command->execute($stdio);
        } catch (\Throwable $e) {
            $stdio->error("Error: {$e->getMessage()}");

            return 1;
        }
    }
}
