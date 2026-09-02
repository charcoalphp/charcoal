<?php

namespace Charcoal\App\ServiceProvider;

use InvalidArgumentException;
// From Pimple
use Pimple\ServiceProviderInterface;
use Pimple\Container;
// From PSR-3
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
// From Monolog
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;
// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;
use Charcoal\Factory\FactoryInterface;
// From 'charcoal-app'
use Charcoal\App\AppConfig;
use Charcoal\App\Config\LoggerConfig;

/**
 * Logger Service Provider
 *
 * Provides a Monolog service to a container.
 *
 * ## Services
 *
 * - `logger` `\Psr\Log\Logger`
 *
 * ## Helpers
 *
 * - `logger/config` `\Charcoal\App\Config\LoggerConfig`
 */
class LoggerServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A service container.
     */
    public function register(Container $container): void
    {
        /**
         * @param  Container $container A service container.
         * @return LoggerConfig
         */
        $container['logger/config'] = function (Container $container): \Charcoal\App\Config\LoggerConfig {
            $loggerConfig = ($container['config']['logger'] ?? null);
            return new LoggerConfig($loggerConfig);
        };

        /**
         * @param  Container $container A service container.
         * @throws InvalidArgumentException If the path is not defined or invalid.
         * @return StreamHandler|null
         */
        $container['logger/handler/stream'] = function (Container $container): ?\Monolog\Handler\StreamHandler {
            $loggerConfig  = $container['logger/config'];
            $handlerConfig = $loggerConfig['handlers.stream'];

            if ($handlerConfig['active'] !== true) {
                return null;
            }

            if (empty($handlerConfig['stream'])) {
                throw new InvalidArgumentException(
                    'No "stream" configured for logger stream handler.'
                );
            }

            $stream = $handlerConfig['stream'];
            if (is_string($stream) && (isset($container['config']) && $container['config'] instanceof AppConfig)) {
                $stream = $container['config']->resolveValue($stream);
            }

            $level = self::resolveLevel($handlerConfig['level'] ?: $loggerConfig['level']);
            return new StreamHandler($stream, $level);
        };

        /**
         * @param  Container $container A service container.
         * @return BrowserConsoleHandler|null
         */
        $container['logger/handler/browser-console'] = function (Container $container): ?\Monolog\Handler\BrowserConsoleHandler {
            $loggerConfig  = $container['logger/config'];
            $handlerConfig = $loggerConfig['handlers.console'];

            if ($handlerConfig['active'] !== true) {
                return null;
            }

            $level = self::resolveLevel($handlerConfig['level'] ?: $loggerConfig['level']);
            return new BrowserConsoleHandler($level);
        };

        /**
         * Fulfills the PSR-3 dependency with a Monolog logger.
         *
         * @param  Container $container A service container.
         * @return LoggerInterface
         */
        $container['logger'] = function (Container $container): \Psr\Log\NullLogger|\Monolog\Logger {
            $loggerConfig = $container['logger/config'];

            if ($loggerConfig['active'] !== true) {
                return new NullLogger();
            }

            $logger = new Logger($loggerConfig['channel']);

            $memProcessor = new MemoryUsageProcessor();
            $logger->pushProcessor($memProcessor);

            $uidProcessor = new UidProcessor();
            $logger->pushProcessor($uidProcessor);

            $consoleHandler = $container['logger/handler/browser-console'];
            if ($consoleHandler) {
                $logger->pushHandler($consoleHandler);
            }

            $streamHandler = $container['logger/handler/stream'];
            if ($streamHandler) {
                $logger->pushHandler($streamHandler);
            }

            return $logger;
        };
    }

    /**
     * @param  string|int|Level|null $level
     * @return Level
     */
    private static function resolveLevel(string|int|Level|null $level): Level
    {
        if ($level instanceof Level) {
            return $level;
        }

        if (is_int($level)) {
            return Level::fromValue($level);
        }

        if (is_string($level)) {
            return Level::fromName(strtolower($level));
        }

        return Level::Debug;
    }
}
