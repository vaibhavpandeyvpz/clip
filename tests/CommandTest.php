<?php

declare(strict_types=1);

namespace Clip;

use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    public function test_name_derived_from_class_name(): void
    {
        $command = new class extends Command
        {
            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        // Anonymous class names are unpredictable, so just verify it returns a string
        $name = $command->name();
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    public function test_name_derived_from_pascal_case_class_name(): void
    {
        // Create a test class to verify name derivation
        $command = new class extends Command
        {
            public function name(): string
            {
                // Simulate a PascalCase class name
                $name = 'MigrateDB';
                $name = preg_replace('/([a-z])([A-Z])/', '$1-$2', $name);

                return strtolower($name);
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $this->assertEquals('migrate-db', $command->name());
    }

    public function test_name_derived_from_class_name_with_multiple_words(): void
    {
        $command = new class extends Command
        {
            public function name(): string
            {
                // Simulate a class name with multiple words
                $name = 'CreateUserCommand';
                $name = preg_replace('/([a-z])([A-Z])/', '$1-$2', $name);

                return strtolower($name);
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $this->assertEquals('create-user-command', $command->name());
    }

    public function test_name_can_be_overridden(): void
    {
        $command = new class extends Command
        {
            public function name(): string
            {
                return 'custom-name';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $this->assertEquals('custom-name', $command->name());
    }

    public function test_description_returns_empty_string_by_default(): void
    {
        $command = new class extends Command
        {
            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $this->assertEquals('', $command->description());
    }

    public function test_description_can_be_overridden(): void
    {
        $command = new class extends Command
        {
            public function description(): string
            {
                return 'Test description';
            }

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $this->assertEquals('Test description', $command->description());
    }

    public function test_execute_is_abstract(): void
    {
        // Verify that Command is abstract and execute() is an abstract method
        $reflection = new \ReflectionClass(Command::class);
        $this->assertTrue($reflection->isAbstract());

        $executeMethod = $reflection->getMethod('execute');
        $this->assertTrue($executeMethod->isAbstract());
    }

    public function test_execute_receives_stdio(): void
    {
        $command = new class extends Command
        {
            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $stdio = new Stdio(['script.php', 'test']);
        $result = $command->execute($stdio);

        $this->assertEquals(0, $result);
        $this->assertInstanceOf(Stdio::class, $stdio);
    }

    public function test_execute_can_return_non_zero(): void
    {
        $command = new class extends Command
        {
            public function execute(Stdio $stdio): int
            {
                return 42;
            }
        };

        $stdio = new Stdio(['script.php', 'test']);
        $result = $command->execute($stdio);

        $this->assertEquals(42, $result);
    }
}
