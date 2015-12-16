<?php

namespace Charcoal\Log;

// PSR-3 logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface as PsrLoggerAwareInterface;

/**
 * Describes a logger-aware instance.
 *
 * Defines methods required by Charcoal's coding standards (`snake_case`, mutator methods).
 *
 * @see \Psr\Log\NullLogger
 *     Logging should always be optional, and if no logger is provided to your
 *     library creating a NullLogger instance to have something to throw logs at
 *     is a good way to avoid littering your code with `if ($this->logger) { }`
 *     blocks.
 */
interface LoggerAwareInterface extends PsrLoggerAwareInterface
{
    /**
     * Set a logger
     *
     * @param  LoggerInterface $logger The PSR-3 compatible logger instance.
     * @return LoggerAwareInterface Chainable
     */
    public function set_logger(LoggerInterface $logger = null);

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger();
}
