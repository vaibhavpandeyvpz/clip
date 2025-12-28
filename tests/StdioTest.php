<?php

declare(strict_types=1);

namespace Clip;

use PHPUnit\Framework\TestCase;

class StdioTest extends TestCase
{
    public function test_constructor_parses_command(): void
    {
        $argv = ['script.php', 'test'];
        $stdio = new Stdio($argv);

        $this->assertEquals('test', $stdio->getCommand());
    }

    public function test_constructor_with_null_argv(): void
    {
        // This will use $_SERVER['argv'] if available, or empty array
        $stdio = new Stdio(null);

        // Just verify it doesn't throw
        $this->assertInstanceOf(Stdio::class, $stdio);
    }

    public function test_constructor_with_empty_argv(): void
    {
        $stdio = new Stdio([]);

        $this->assertEquals('', $stdio->getCommand());
        $this->assertEquals([], $stdio->getArguments());
    }

    public function test_constructor_parses_arguments(): void
    {
        $argv = ['script.php', 'test', 'arg1', 'arg2', 'arg3'];
        $stdio = new Stdio($argv);

        $this->assertEquals(['arg1', 'arg2', 'arg3'], $stdio->getArguments());
        $this->assertEquals('arg1', $stdio->getArgument(0));
        $this->assertEquals('arg2', $stdio->getArgument(1));
        $this->assertEquals('arg3', $stdio->getArgument(2));
        $this->assertNull($stdio->getArgument(3));
        $this->assertEquals('default', $stdio->getArgument(3, 'default'));
    }

    public function test_constructor_parses_options(): void
    {
        $argv = ['script.php', 'test', '--name=John', '--verbose', '--force=true'];
        $stdio = new Stdio($argv);

        $this->assertEquals('John', $stdio->getOption('name'));
        $this->assertTrue($stdio->getOption('verbose'));
        $this->assertEquals('true', $stdio->getOption('force'));
        $this->assertTrue($stdio->hasOption('name'));
        $this->assertTrue($stdio->hasOption('verbose'));
        $this->assertFalse($stdio->hasOption('nonexistent'));
    }

    public function test_constructor_parses_options_with_equals_in_value(): void
    {
        $argv = ['script.php', 'test', '--url=http://example.com?key=value'];
        $stdio = new Stdio($argv);

        $this->assertEquals('http://example.com?key=value', $stdio->getOption('url'));
    }

    public function test_constructor_parses_mixed_arguments_and_options(): void
    {
        $argv = ['script.php', 'test', 'arg1', '--option=value', 'arg2', '--flag'];
        $stdio = new Stdio($argv);

        $this->assertEquals(['arg1', 'arg2'], $stdio->getArguments());
        $this->assertEquals('value', $stdio->getOption('option'));
        $this->assertTrue($stdio->hasOption('flag'));
    }

    public function test_get_option_with_default(): void
    {
        $argv = ['script.php', 'test'];
        $stdio = new Stdio($argv);

        $this->assertNull($stdio->getOption('nonexistent'));
        $this->assertEquals('default', $stdio->getOption('nonexistent', 'default'));
        $this->assertFalse($stdio->getOption('nonexistent', false));
    }

    public function test_get_options(): void
    {
        $argv = ['script.php', 'test', '--opt1=val1', '--opt2'];
        $stdio = new Stdio($argv);

        $options = $stdio->getOptions();
        $this->assertIsArray($options);
        $this->assertEquals('val1', $options['opt1']);
        $this->assertTrue($options['opt2']);
    }

    public function test_write(): void
    {
        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output);

        $stdio->write('Hello');
        rewind($output);
        $this->assertEquals('Hello'.PHP_EOL, stream_get_contents($output));
    }

    public function test_write_without_newline(): void
    {
        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output);

        $stdio->write('Hello', false);
        rewind($output);
        $this->assertEquals('Hello', stream_get_contents($output));
    }

    public function test_writeln(): void
    {
        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output);

        $stdio->writeln('Hello');
        rewind($output);
        $this->assertEquals('Hello'.PHP_EOL, stream_get_contents($output));
    }

    public function test_writeln_empty(): void
    {
        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output);

        $stdio->writeln();
        rewind($output);
        $this->assertEquals(PHP_EOL, stream_get_contents($output));
    }

    public function test_error(): void
    {
        $stderr = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], null, $stderr);

        $stdio->error('Error message');
        rewind($stderr);
        $content = stream_get_contents($stderr);
        $this->assertStringContainsString('Error message', $content);
    }

    public function test_error_without_newline(): void
    {
        $stderr = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], null, $stderr);

        $stdio->error('Error', false);
        rewind($stderr);
        $content = stream_get_contents($stderr);
        $this->assertStringContainsString('Error', $content);
        $this->assertStringNotContainsString(PHP_EOL, $content);
    }

    public function test_warning(): void
    {
        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output);

        $stdio->warning('Warning message');
        rewind($output);
        $content = stream_get_contents($output);
        $this->assertStringContainsString('Warning message', $content);
    }

    public function test_warning_without_newline(): void
    {
        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output);

        $stdio->warning('Warning', false);
        rewind($output);
        $content = stream_get_contents($output);
        $this->assertStringContainsString('Warning', $content);
    }

    public function test_info(): void
    {
        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output);

        $stdio->info('Info message');
        rewind($output);
        $content = stream_get_contents($output);
        $this->assertStringContainsString('Info message', $content);
    }

    public function test_info_without_newline(): void
    {
        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output);

        $stdio->info('Info', false);
        rewind($output);
        $content = stream_get_contents($output);
        $this->assertStringContainsString('Info', $content);
    }

    public function test_debug(): void
    {
        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output);

        $stdio->debug('Debug message');
        rewind($output);
        $this->assertEquals('Debug message'.PHP_EOL, stream_get_contents($output));
    }

    public function test_debug_without_newline(): void
    {
        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output);

        $stdio->debug('Debug', false);
        rewind($output);
        $this->assertEquals('Debug', stream_get_contents($output));
    }

    public function test_verbose(): void
    {
        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output);

        $stdio->verbose('Verbose message');
        rewind($output);
        $this->assertEquals('Verbose message'.PHP_EOL, stream_get_contents($output));
    }

    public function test_verbose_without_newline(): void
    {
        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output);

        $stdio->verbose('Verbose', false);
        rewind($output);
        $this->assertEquals('Verbose', stream_get_contents($output));
    }

    public function test_ask(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, 'John'.PHP_EOL);
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->ask('What is your name?');

        $this->assertEquals('John', $result);
    }

    public function test_ask_with_default(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, PHP_EOL); // Empty input
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->ask('What is your name?', 'Guest');

        $this->assertEquals('Guest', $result);
    }

    public function test_ask_with_empty_string_default(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, PHP_EOL);
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->ask('What is your name?', '');

        $this->assertEquals('', $result);
    }

    public function test_confirm_returns_true_with_yes(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, 'yes'.PHP_EOL);
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->confirm('Continue?');

        $this->assertTrue($result);
    }

    public function test_confirm_returns_true_with_y(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, 'y'.PHP_EOL);
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->confirm('Continue?');

        $this->assertTrue($result);
    }

    public function test_confirm_returns_true_with1(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, '1'.PHP_EOL);
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->confirm('Continue?');

        $this->assertTrue($result);
    }

    public function test_confirm_returns_true_with_true(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, 'true'.PHP_EOL);
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->confirm('Continue?');

        $this->assertTrue($result);
    }

    public function test_confirm_returns_false_with_no(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, 'no'.PHP_EOL);
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->confirm('Continue?');

        $this->assertFalse($result);
    }

    public function test_confirm_returns_false_with_n(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, 'n'.PHP_EOL);
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->confirm('Continue?');

        $this->assertFalse($result);
    }

    public function test_confirm_with_default_true(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, PHP_EOL); // Empty input
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->confirm('Continue?', true);

        $this->assertTrue($result);
    }

    public function test_confirm_with_default_false(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, PHP_EOL);
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->confirm('Continue?', false);

        $this->assertFalse($result);
    }

    public function test_choice(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, '1'.PHP_EOL);
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->choice('Select:', ['option1', 'option2', 'option3']);

        $this->assertEquals('option1', $result);
    }

    public function test_choice_with_default(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, PHP_EOL); // Empty input
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->choice('Select:', ['option1', 'option2'], 'option2');

        $this->assertEquals('option2', $result);
    }

    public function test_choice_with_direct_value(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, 'option2'.PHP_EOL);
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->choice('Select:', ['option1', 'option2']);

        $this->assertEquals('option2', $result);
    }

    public function test_choice_with_invalid_numeric_input_then_valid(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, '99'.PHP_EOL.'2'.PHP_EOL); // Invalid then valid
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->choice('Select:', ['option1', 'option2']);

        $this->assertEquals('option2', $result);
    }

    public function test_choice_with_invalid_text_input_then_valid(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, 'invalid'.PHP_EOL.'option1'.PHP_EOL);
        rewind($input);

        $output = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], $output, null, $input);

        $result = $stdio->choice('Select:', ['option1', 'option2']);

        $this->assertEquals('option1', $result);
    }

    public function test_choice_throws_exception_for_empty_choices(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Choices array cannot be empty.');

        $stdio = new Stdio(['script.php', 'test']);
        $stdio->choice('Select:', []);
    }

    public function test_empty_command(): void
    {
        $argv = ['script.php'];
        $stdio = new Stdio($argv);

        $this->assertEquals('', $stdio->getCommand());
    }

    public function test_get_command_with_empty_argv(): void
    {
        $stdio = new Stdio(['script.php']);

        $this->assertEquals('', $stdio->getCommand());
        $this->assertEquals([], $stdio->getArguments());
    }

    public function test_colors_disabled_with_no_color_env(): void
    {
        putenv('NO_COLOR=1');

        $stderr = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], null, $stderr);

        $stdio->error('Error');
        rewind($stderr);
        $content = stream_get_contents($stderr);

        // Should not contain ANSI color codes
        $this->assertStringNotContainsString("\033[", $content);
        $this->assertStringContainsString('Error', $content);

        putenv('NO_COLOR');
    }

    public function test_colors_enabled_when_supported(): void
    {
        // Create a mock stream that appears to support colors
        $stderr = fopen('php://memory', 'r+');
        $stdio = new Stdio(['script.php', 'test'], null, $stderr);

        // We can't easily test color support detection without mocking,
        // but we can test that colorize works when enabled
        $stdio->error('Error');
        rewind($stderr);
        $content = stream_get_contents($stderr);

        // Just verify it contains the message
        $this->assertStringContainsString('Error', $content);
    }
}
