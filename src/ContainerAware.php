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

use Psr\Container\ContainerInterface;

/**
 * Trait that provides PSR-11 container awareness.
 *
 * Classes using this trait can access services from a PSR-11 container.
 */
trait ContainerAware
{
    /**
     * PSR-11 container instance.
     */
    protected ?ContainerInterface $container = null;

    /**
     * Sets the PSR-11 container instance.
     *
     * @param  ContainerInterface  $container  The container instance
     * @return $this
     */
    public function container(ContainerInterface $container): static
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Gets a service from the container.
     *
     * @param  string  $id  The service identifier
     * @return mixed The service instance
     *
     * @throws \Psr\Container\ContainerExceptionInterface If the service cannot be retrieved
     * @throws \Psr\Container\NotFoundExceptionInterface If the service is not found
     * @throws \RuntimeException If the container is not available
     */
    protected function get(string $id): mixed
    {
        if ($this->container === null) {
            throw new \RuntimeException('Container is not available. Pass a PSR-11 container to Console constructor.');
        }

        return $this->container->get($id);
    }

    /**
     * Checks if a service exists in the container.
     *
     * @param  string  $id  The service identifier
     * @return bool True if the service exists, false otherwise
     */
    protected function has(string $id): bool
    {
        if ($this->container === null) {
            return false;
        }

        return $this->container->has($id);
    }
}
