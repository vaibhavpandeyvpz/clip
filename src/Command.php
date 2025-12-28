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
 * Abstract base class for console commands.
 *
 * Provides helper methods for common command operations.
 */
abstract class Command
{
    use ContainerAware;

    /**
     * Returns the name of the command.
     *
     * By default, derives the name from the class name by converting
     * PascalCase to kebab-case (e.g., MigrateDB -> migrate-db).
     *
     * @return string The command name
     */
    public function getName(): string
    {
        $name = static::class;
        $name = substr($name, strrpos($name, '\\') + 1);
        $name = preg_replace('/([a-z])([A-Z])/', '$1-$2', $name);

        return strtolower($name);
    }

    /**
     * Returns the description of the command.
     *
     * @return string The command description (empty string by default)
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * Executes the command with the given stdio.
     *
     * @param  Stdio  $stdio  The stdio containing input and output
     * @return int The exit code (0 for success, non-zero for failure)
     */
    abstract public function execute(Stdio $stdio): int;
}
