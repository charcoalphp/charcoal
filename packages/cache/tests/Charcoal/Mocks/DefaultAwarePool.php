<?php

namespace Charcoal\Tests\Mocks;

use Charcoal\Tests\Cache\Factory\CacheBuilderPoolTest;
use Psr\Log\LoggerInterface;

/**
 * Mock object for {@see CacheBuilderPoolTest}
 */
class DefaultAwarePool extends \Stash\Pool
{
    public function getItemClass():string
    {
        return $this->itemClass;
    }

    /**
     * @return Boolean|LoggerInterface
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }
}
