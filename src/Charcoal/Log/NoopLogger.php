<?php

namespace Charcoal\Log;

// PSR-3 logger
use \Psr\Log\LoggerInterface;

/**
 * This Logger can be used to avoid conditional log calls.
 *
 * Logging should always be optional, and if no logger is provided to your library
 * creating a NoopLogger instance to have something to throw logs at is a good
 * way to avoid littering your code with `if ($this->logger) { }` blocks.
 *
 * @link https://github.com/php-fig/log/blob/master/Psr/Log/NullLogger.php
 *     For example of an mock logger.
 */
class NoopLogger implements LoggerInterface
{
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function log($level, $message, array $context = [])
    {
        // noop
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function emergency($message, array $context = [])
    {
        // noop
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function alert($message, array $context = [])
    {
        // noop
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function critical($message, array $context = [])
    {
        // noop
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function error($message, array $context = [])
    {
        // noop
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function warning($message, array $context = [])
    {
        // noop
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function notice($message, array $context = [])
    {
        // noop
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function info($message, array $context = [])
    {
        // noop
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function debug($message, array $context = [])
    {
        // noop
    }
}
