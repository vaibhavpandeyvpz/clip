<?php

declare(strict_types=1);

namespace Clip;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
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

        $container = $this->createMock(ContainerInterface::class);
        $result = $command->container($container);

        $this->assertSame($command, $result);
    }

    public function test_get_service_from_container(): void
    {
        $service = new \stdClass;
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with('service.name')
            ->willReturn($service);

        $command = new class extends Command
        {
            use ContainerAware;

            public function execute(Stdio $stdio): int
            {
                $service = $this->get('service.name');

                return 0;
            }
        };

        $command->container($container);
        $stdio = new Stdio(['script.php', 'test']);

        $result = $command->execute($stdio);

        $this->assertEquals(0, $result);
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
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->with('service.name')
            ->willReturn(true);

        $command = new class extends Command
        {
            use ContainerAware;

            public function execute(Stdio $stdio): int
            {
                $exists = $this->has('service.name');

                return $exists ? 0 : 1;
            }
        };

        $command->container($container);
        $stdio = new Stdio(['script.php', 'test']);

        $result = $command->execute($stdio);

        $this->assertEquals(0, $result);
    }

    public function test_has_returns_false_when_service_does_not_exist(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->with('service.name')
            ->willReturn(false);

        $command = new class extends Command
        {
            use ContainerAware;

            public function execute(Stdio $stdio): int
            {
                $exists = $this->has('service.name');

                return $exists ? 0 : 1;
            }
        };

        $command->container($container);
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
        $exception = $this->createMock(NotFoundExceptionInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with('service.name')
            ->willThrowException($exception);

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

        $command->container($container);
        $stdio = new Stdio(['script.php', 'test']);

        $result = $command->execute($stdio);

        $this->assertEquals(1, $result);
    }

    public function test_get_propagates_container_exception(): void
    {
        $exception = $this->createMock(ContainerExceptionInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with('service.name')
            ->willThrowException($exception);

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

        $command->container($container);
        $stdio = new Stdio(['script.php', 'test']);

        $result = $command->execute($stdio);

        $this->assertEquals(1, $result);
    }
}
