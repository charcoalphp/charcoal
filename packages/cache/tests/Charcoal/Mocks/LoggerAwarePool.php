<?php

namespace Charcoal\Tests\Mocks;

use Psr\Log\LoggerInterface;

/**
 * Mock object for {@see \Charcoal\Tests\Cache\Factory\CacheBuilderPoolTest}
 */
class LoggerAwarePool extends \Stash\Pool
{
    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
