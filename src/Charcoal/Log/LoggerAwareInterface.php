<?php

namespace Charcoal\Log;

// PSR-3 logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface as PsrLoggerAwareInterface;

/**
 * Describes a logger-aware instance.
 *
 * Defines methods required by Charcoal's coding standards (`snake_case`, mutator methods).
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
