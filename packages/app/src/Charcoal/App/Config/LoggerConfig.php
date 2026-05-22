<?php

namespace Charcoal\App\Config;

use InvalidArgumentException;
// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Logger Configuration
 */
class LoggerConfig extends AbstractConfig
{
    public const DEFAULT_CHANNEL = 'charcoal';

    /**
     * Whether to enable or disable the logger service.
     */
    private ?bool $active = null;

    /**
     * Record handler(s) to use.
     *
     * Whenever you add a record to the logger, it traverses the handler stack.
     */
    private ?array $handlers = null;

    /**
     * Record processor(s) to use.
     *
     * For customizing records added to the logger.
     */
    private ?array $processors = null;

    /**
     * Channel name.
     */
    private string $channel = self::DEFAULT_CHANNEL;

    /**
     * Retrieve the default values.
     */
    #[\Override]
    public function defaults(): array
    {
        return [
            'active'    => true,
            'channel'   => self::DEFAULT_CHANNEL,
            'level'     => 'debug',
            'handlers'  => [
                'stream' => [
                    'type'   => 'stream',
                    'stream' => '%app.logs_path%/charcoal.app.log',
                    'level'  => null,
                    'bubble' => true,
                    'active' => true,
                ],
                'console' => [
                    'type'   => 'browser-console',
                    'level'  => null,
                    'active' => false,
                ]
            ],
            'processors' => [
                [
                    'type' => 'memory-usage',
                ],
                [
                    'type' => 'uid',
                ],
            ],
        ];
    }

    /**
     * Enable / Disable the logger service.
     *
     * @param  boolean $active The active flag;
     *     TRUE to enable, FALSE to disable.
     * @return LoggerConfig Chainable
     */
    public function setActive($active): static
    {
        $this->active = (bool)$active;
        return $this;
    }

    /**
     * Determine if the logger service is enabled.
     *
     * @return boolean TRUE if enabled, FALSE if disabled.
     */
    public function active(): ?bool
    {
        return $this->active;
    }

    /**
     * Set the record handler(s) to use.
     *
     * @param  array $handlers One or more (Monolog) record handlers; used as a stack.
     */
    public function setHandlers(array $handlers): static
    {
        $this->handlers = [];
        $this->addHandlers($handlers);
        return $this;
    }

    /**
     * Add record handler(s) to use.
     *
     * @param  string[] $handlers One or more (Monolog) handlers to stack.
     */
    public function addHandlers(array $handlers): static
    {
        foreach ($handlers as $key => $handler) {
            $this->addHandler($handler, $key);
        }
        return $this;
    }

    /**
     * Add a record handler to use.
     *
     * @param  array       $handler The record handler structure.
     * @param  string|null $key     The handler's key.
     * @throws InvalidArgumentException If the handler is invalid.
     */
    public function addHandler(array $handler, $key = null): static
    {
        if (!isset($handler['type'])) {
            throw new InvalidArgumentException(
                'Handler type is required.'
            );
        }

        if (!is_string($key)) {
            $this->handlers[] = $handler;
        } else {
            $this->handlers[$key] = $handler;
        }

        return $this;
    }

    /**
     * Retrieve the record handler(s) to use.
     *
     * @return array
     */
    public function handlers(): ?array
    {
        return $this->handlers;
    }

    /**
     * Set the record processor(s) to use.
     *
     * @param  array $processors One or more (Monolog) record processors; used as a stack.
     */
    public function setProcessors(array $processors): static
    {
        $this->processors = [];
        $this->addProcessors($processors);
        return $this;
    }

    /**
     * Add record processor(s) to use.
     *
     * @param  string[] $processors One or more (Monolog) processors to stack.
     */
    public function addProcessors(array $processors): static
    {
        foreach ($processors as $key => $processor) {
            $this->addProcessor($processor, $key);
        }
        return $this;
    }

    /**
     * Add a record processor to use.
     *
     * @param  array       $processor The record processor structure.
     * @param  string|null $key       The processor's key.
     * @throws InvalidArgumentException If the processor is invalid.
     */
    public function addProcessor(array $processor, $key = null): static
    {
        if (!isset($processor['type'])) {
            throw new InvalidArgumentException(
                'Processor type is required.'
            );
        }

        if (!is_string($key)) {
            $this->processors[] = $processor;
        } else {
            $this->processors[$key] = $processor;
        }

        return $this;
    }

    /**
     * Retrieve the record processor(s) to use.
     *
     * @return array
     */
    public function processors(): ?array
    {
        return $this->processors;
    }

    /**
     * Set the channel name.
     *
     * @param  string $name The channe name (namespace).
     * @throws InvalidArgumentException If the channel name is not a string.
     */
    public function setChannel($name): static
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                'Channel name must be a string.'
            );
        }

        $this->channel = $name;
        return $this;
    }

    /**
     * Retrieve the cache namespace.
     */
    public function channel(): string
    {
        return $this->channel;
    }
}
