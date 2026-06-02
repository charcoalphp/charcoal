<?php

namespace Charcoal\View\Mustache;

use InvalidArgumentException;
use RuntimeException;
use Traversable;
// From Mustache
use Mustache\Engine as Mustache_Engine;
// From 'charcoal-view'
use Charcoal\View\AbstractEngine;

/**
 * Mustache view rendering engine.
 */
class MustacheEngine extends AbstractEngine
{
    public const DEFAULT_CACHE_PATH = '../cache/mustache';

    /**
     * A collection of helpers.
     *
     * @var array
     */
    private $helpers = [];

    /**
     * The renderering framework.
     */
    private ?Mustache_Engine $mustache = null;

    public function type(): string
    {
        return 'mustache';
    }

    /**
     * Build the Mustache Engine with an array of dependencies.
     *
     * @param array $data Engine dependencie.
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        if (isset($data['helpers'])) {
            $this->setHelpers($data['helpers']);
        }
    }

    /**
     * Set the engine's helpers.
     *
     * @param  array|Traversable|HelpersInterface $helpers Mustache helpers.
     * @throws InvalidArgumentException If the given helper(s) are invalid.
     */
    public function setHelpers($helpers): static
    {
        if ($helpers instanceof HelpersInterface) {
            $helpers = $helpers->toArray();
        }

        if (!is_array($helpers) && !$helpers instanceof Traversable) {
            throw new InvalidArgumentException(sprintf(
                'setHelpers expects an array of helpers, received %s',
                (get_debug_type($helpers))
            ));
        }

        $this->helpers = [];
        foreach ($helpers as $name => $helper) {
            $this->addHelper($name, $helper);
        }

        return $this;
    }

    /**
     * Merge (replacing or adding) the engine's helpers.
     *
     * @param  array|Traversable|HelpersInterface $helpers Mustache helpers.
     * @throws InvalidArgumentException If the given helper(s) are invalid.
     */
    public function mergeHelpers($helpers): static
    {
        if ($helpers instanceof HelpersInterface) {
            $helpers = $helpers->toArray();
        }

        if (!is_array($helpers) && !$helpers instanceof Traversable) {
            throw new InvalidArgumentException(sprintf(
                'mergeHelpers expects an array of helpers, received %s',
                (get_debug_type($helpers))
            ));
        }

        foreach ($helpers as $name => $helper) {
            $this->addHelper($name, $helper);
        }

        return $this;
    }

    /**
     * Add a helper.
     *
     * @param  string $name   The tag name.
     * @param  mixed  $helper The tag value.
     * @throws RuntimeException If the mustache engine was already initialized.
     */
    public function addHelper(string $name, $helper): static
    {
        if ($this->mustache instanceof Mustache_Engine) {
            throw new RuntimeException(
                'Can not add helper to Mustache engine: the engine has already been initialized.'
            );
        }

        $this->helpers[$name] = $helper;

        return $this;
    }

    /**
     * Retrieve the engine's helpers.
     */
    public function helpers(): array
    {
        return $this->helpers;
    }

    /**
     * @param string $templateIdent The template identifier to load and render.
     * @param mixed  $context       The rendering context.
     * @return string The rendered template string.
     */
    #[\Override]
    public function render(string $templateIdent, $context): string
    {
        return $this->mustache()->render($templateIdent, $context);
    }

    /**
     * @param string $templateString The template string to render.
     * @param mixed  $context        The rendering context.
     * @return string The rendered template string.
     */
    public function renderTemplate(string $templateString, $context): string
    {
        return $this->mustache()->render($templateString, $context);
    }

    protected function mustache(): Mustache_Engine
    {
        if (!$this->mustache instanceof Mustache_Engine) {
            $this->mustache = $this->createMustache();
        }

        return $this->mustache;
    }

    protected function createMustache(): Mustache_Engine
    {
        return new Mustache_Engine([
            'cache'             => $this->cache(),
            'loader'            => $this->loader(),
            'partials_loader'   => $this->loader(),
            'strict_callables'  => true,
            'helpers'           => $this->helpers()
        ]);
    }

    /**
     * Set the engine's cache implementation.
     *
     * @param  mixed $cache A Mustache cache option.
     */
    #[\Override]
    protected function setCache($cache): void
    {
        /**
         * If FALSE is specified, the value is converted to NULL
         * because Mustache internally requires NULL to disable the cache.
         */
        if ($cache === false) {
            $cache = null;
        }

        parent::setCache($cache);
    }
}
