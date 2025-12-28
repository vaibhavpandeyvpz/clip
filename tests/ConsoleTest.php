<?php

declare(strict_types=1);

namespace Clip;

use Katora\Container;
use PHPUnit\Framework\TestCase;

class ConsoleTest extends TestCase
{
    public function test_constructor_with_empty_array(): void
    {
        $console = new Console([]);

        $this->assertInstanceOf(Console::class, $console);
    }

    public function test_constructor_with_commands(): void
    {
        $command = new class extends Command
        {
            public function name(): string
            {
                return 'test';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $console = new Console([$command::class]);

        $this->assertInstanceOf(Console::class, $console);
    }

    public function test_constructor_with_container(): void
    {
        $container = new Container;
        $console = new Console([], $container);

        $this->assertInstanceOf(Console::class, $console);
    }

    public function test_constructor_with_commands_and_container(): void
    {
        $container = new Container;
        $command = new class extends Command
        {
            use ContainerAware;

            public function name(): string
            {
                return 'test';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $console = new Console([$command::class], $container);

        $this->assertInstanceOf(Console::class, $console);
    }

    public function test_command_method_returns_self(): void
    {
        $command = new class extends Command
        {
            public function name(): string
            {
                return 'test';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $console = new Console;
        $result = $console->command($command::class);

        $this->assertSame($console, $result);
    }

    public function test_command_method_can_be_chained(): void
    {
        $command1 = new class extends Command
        {
            public function name(): string
            {
                return 'test1';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $command2 = new class extends Command
        {
            public function name(): string
            {
                return 'test2';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $console = new Console;
        $result = $console->command($command1::class)->command($command2::class);

        $this->assertSame($console, $result);
    }

    public function test_run_without_command_lists_commands(): void
    {
        $command = new class extends Command
        {
            public function name(): string
            {
                return 'test';
            }

            public function description(): string
            {
                return 'Test command';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $console = new Console([$command::class]);
        $argv = ['script.php'];

        $result = $console->run($argv);

        $this->assertEquals(0, $result);
    }

    public function test_run_without_command_with_empty_commands(): void
    {
        $console = new Console([]);
        $argv = ['script.php'];

        $result = $console->run($argv);

        $this->assertEquals(0, $result);
    }

    public function test_run_with_null_argv(): void
    {
        $command = new class extends Command
        {
            public function name(): string
            {
                return 'test';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $console = new Console([$command::class]);

        // This will use $_SERVER['argv'] or empty array
        $result = $console->run(null);

        // Should not throw, result depends on $_SERVER['argv']
        $this->assertIsInt($result);
    }

    public function test_run_with_valid_command(): void
    {
        $command = new class extends Command
        {
            public function name(): string
            {
                return 'test';
            }

            public function execute(Stdio $stdio): int
            {
                $stdio->writeln('Hello');

                return 0;
            }
        };

        $console = new Console([$command::class]);
        $argv = ['script.php', 'test'];

        $result = $console->run($argv);

        $this->assertEquals(0, $result);
    }

    public function test_run_with_command_returning_non_zero(): void
    {
        $command = new class extends Command
        {
            public function name(): string
            {
                return 'test';
            }

            public function execute(Stdio $stdio): int
            {
                return 1;
            }
        };

        $console = new Console([$command::class]);
        $argv = ['script.php', 'test'];

        $result = $console->run($argv);

        $this->assertEquals(1, $result);
    }

    public function test_run_with_invalid_command(): void
    {
        $console = new Console([]);
        $argv = ['script.php', 'nonexistent'];

        $result = $console->run($argv);

        $this->assertEquals(1, $result);
    }

    public function test_run_with_command_that_throws_exception(): void
    {
        $command = new class extends Command
        {
            public function name(): string
            {
                return 'test';
            }

            public function execute(Stdio $stdio): int
            {
                throw new \RuntimeException('Test error');
            }
        };

        $console = new Console([$command::class]);
        $argv = ['script.php', 'test'];

        $result = $console->run($argv);

        $this->assertEquals(1, $result);
    }

    public function test_run_with_command_that_throws_error(): void
    {
        $command = new class extends Command
        {
            public function name(): string
            {
                return 'test';
            }

            public function execute(Stdio $stdio): int
            {
                throw new \Error('Fatal error');
            }
        };

        $console = new Console([$command::class]);
        $argv = ['script.php', 'test'];

        $result = $console->run($argv);

        $this->assertEquals(1, $result);
    }

    public function test_resolve_command_with_class_string(): void
    {
        $command = new class extends Command
        {
            public function name(): string
            {
                return 'test';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $console = new Console([$command::class]);
        $argv = ['script.php', 'test'];

        $result = $console->run($argv);

        $this->assertEquals(0, $result);
    }

    public function test_resolve_command_with_instance(): void
    {
        $command = new class extends Command
        {
            public function name(): string
            {
                return 'test';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $console = new Console([$command]);
        $argv = ['script.php', 'test'];

        $result = $console->run($argv);

        $this->assertEquals(0, $result);
    }

    public function test_resolve_command_with_container(): void
    {
        $container = new Container;
        $container->set('service', new \stdClass);

        $command = new class extends Command
        {
            use ContainerAware;

            public function name(): string
            {
                return 'test';
            }

            public function execute(Stdio $stdio): int
            {
                // Verify container is set and service exists
                if (! $this->has('service')) {
                    return 1;
                }

                return 0;
            }
        };

        $console = new Console([$command::class], $container);
        $argv = ['script.php', 'test'];

        $result = $console->run($argv);

        $this->assertEquals(0, $result);
        $this->assertTrue($container->has('service'));
    }

    public function test_resolve_command_with_container_but_no_container_method(): void
    {
        $container = new Container;

        $command = new class extends Command
        {
            // Does not use ContainerAware trait

            public function name(): string
            {
                return 'test';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $console = new Console([$command::class], $container);
        $argv = ['script.php', 'test'];

        // Should not throw, container method doesn't exist so it's skipped
        $result = $console->run($argv);

        $this->assertEquals(0, $result);
    }

    public function test_resolve_command_throws_exception_for_non_existent_class(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Command class 'NonExistentClass' not found.");

        $console = new Console(['NonExistentClass']);
        $argv = ['script.php', 'test'];

        $console->run($argv);
    }

    public function test_resolve_command_throws_exception_for_invalid_class(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Command class 'stdClass' must extend Command.");

        $console = new Console([\stdClass::class]);
        $argv = ['script.php', 'test'];

        $console->run($argv);
    }

    public function test_multiple_commands_with_different_names(): void
    {
        $command1 = new class extends Command
        {
            public function name(): string
            {
                return 'command1';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $command2 = new class extends Command
        {
            public function name(): string
            {
                return 'command2';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $console = new Console([$command1::class, $command2::class]);
        $argv = ['script.php', 'command1'];

        $result = $console->run($argv);

        $this->assertEquals(0, $result);
    }

    public function test_list_commands_with_multiple_commands(): void
    {
        $command1 = new class extends Command
        {
            public function name(): string
            {
                return 'cmd1';
            }

            public function description(): string
            {
                return 'First command';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $command2 = new class extends Command
        {
            public function name(): string
            {
                return 'cmd2';
            }

            public function description(): string
            {
                return 'Second command';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $console = new Console([$command1::class, $command2::class]);
        $argv = ['script.php'];

        $result = $console->run($argv);

        $this->assertEquals(0, $result);
    }

    public function test_get_command_returns_null_for_non_existent_command(): void
    {
        $console = new Console([]);
        $argv = ['script.php', 'nonexistent'];

        $result = $console->run($argv);

        $this->assertEquals(1, $result);
    }
}
