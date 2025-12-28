<?php

declare(strict_types=1);

namespace Clip;

use Katora\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerAwareTest extends TestCase
{
    public function test_set_container_returns_self(): void
    {
        $command = new class extends Command
        {
            use ContainerAware;

            public function execute(Stdio $stdio): int
            {
                return 0;
            }
        };

        $container = new Container;
        $result = $command->setContainer($container);

        $this->assertSame($command, $result);
    }

    public function test_get_service_from_container(): void
    {
        $service = new \stdClass;
        $container = new Container;
        $container->set('service.name', $service);

        $command = new class extends Command
        {
            use ContainerAware;

            public function execute(Stdio $stdio): int
            {
                $service = $this->get('service.name');

                return 0;
            }
        };

        $command->setContainer($container);
        $stdio = new Stdio(['script.php', 'test']);

        $result = $command->execute($stdio);

        $this->assertEquals(0, $result);
        $this->assertInstanceOf(\stdClass::class, $container->get('service.name'));
    }

    public function test_get_throws_exception_when_container_not_set(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Container is not available. Pass a PSR-11 container to Console constructor.');

        $command = new class extends Command
        {
            use ContainerAware;

            public function execute(Stdio $stdio): int
            {
                $this->get('service.name');

                return 0;
            }
        };

        $stdio = new Stdio(['script.php', 'test']);
        $command->execute($stdio);
    }

    public function test_has_returns_true_when_service_exists(): void
    {
        $container = new Container;
        $container->set('service.name', new \stdClass);

        $command = new class extends Command
        {
            use ContainerAware;

            public function execute(Stdio $stdio): int
            {
                $exists = $this->has('service.name');

                return $exists ? 0 : 1;
            }
        };

        $command->setContainer($container);
        $stdio = new Stdio(['script.php', 'test']);

        $result = $command->execute($stdio);

        $this->assertEquals(0, $result);
    }

    public function test_has_returns_false_when_service_does_not_exist(): void
    {
        $container = new Container;

        $command = new class extends Command
        {
            use ContainerAware;

            public function execute(Stdio $stdio): int
            {
                $exists = $this->has('service.name');

                return $exists ? 0 : 1;
            }
        };

        $command->setContainer($container);
        $stdio = new Stdio(['script.php', 'test']);

        $result = $command->execute($stdio);

        $this->assertEquals(1, $result);
    }

    public function test_has_returns_false_when_container_not_set(): void
    {
        $command = new class extends Command
        {
            use ContainerAware;

            public function execute(Stdio $stdio): int
            {
                $exists = $this->has('service.name');

                return $exists ? 0 : 1;
            }
        };

        $stdio = new Stdio(['script.php', 'test']);

        $result = $command->execute($stdio);

        $this->assertEquals(1, $result);
    }

    public function test_get_propagates_not_found_exception(): void
    {
        $container = new Container;

        $command = new class extends Command
        {
            use ContainerAware;

            public function execute(Stdio $stdio): int
            {
                try {
                    $this->get('service.name');
                } catch (NotFoundExceptionInterface $e) {
                    return 1;
                }

                return 0;
            }
        };

        $command->setContainer($container);
        $stdio = new Stdio(['script.php', 'test']);

        $result = $command->execute($stdio);

        $this->assertEquals(1, $result);
    }

    public function test_get_propagates_container_exception(): void
    {
        // Katora will throw NotFoundException for non-existent services
        // which implements ContainerExceptionInterface
        $container = new Container;

        $command = new class extends Command
        {
            use ContainerAware;

            public function execute(Stdio $stdio): int
            {
                try {
                    $this->get('service.name');
                } catch (ContainerExceptionInterface $e) {
                    return 1;
                }

                return 0;
            }
        };

        $command->setContainer($container);
        $stdio = new Stdio(['script.php', 'test']);

        $result = $command->execute($stdio);

        $this->assertEquals(1, $result);
    }
}
