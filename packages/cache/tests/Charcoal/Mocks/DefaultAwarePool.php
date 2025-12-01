<?php

namespace Charcoal\Tests\Mocks;

use Psr\Log\LoggerInterface;

/**
 * Mock object for {@see CacheBuilderPoolTest}
 */
class DefaultAwarePool extends \Stash\Pool
{
    /**
     * @return string
     */
    public function getItemClass(): string
    {
        return $this->itemClass;
    }

    public function getNamespace(): bool|string
    {
        return ($this->namespace ?? false);
    }

    /**
     * @return Boolean|LoggerInterface
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }
}
