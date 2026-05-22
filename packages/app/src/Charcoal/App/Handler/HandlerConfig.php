<?php

namespace Charcoal\App\Handler;

use InvalidArgumentException;
// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;
// From 'charcoal-app'
use Charcoal\App\App;
use Charcoal\App\Template\GenericTemplate;

/**
 * HTTP Application Handler Configset
 */
class HandlerConfig extends AbstractConfig
{
    /**
     * The view (to load).
     */
    private ?string $template = null;

    /**
     * The view engine ident to use.
     *
     * For example: "mustache"
     */
    private ?string $engine = null;

    /**
     * The view controller.
     */
    private ?string $controller = null;

    /**
     * Dynamic views to register.
     *
     * @var array
     */
    private $partials = [
        'handlerMessage' => null
    ];

    /**
     * Additional view data.
     */
    private array $templateData = [];

    /**
     * Enable handler-level caching for the view.
     */
    private bool $cache = false;

    /**
     * Time-to-live, in seconds, of the cache. (0 = no limit).
     */
    private int $cacheTtl = 0;

    /**
     * Retrieve the default handler types.
     */
    public static function defaultHandlerTypes(): array
    {
        return [
            'maintenance',
            'notFound',
            'notAllowed',
            'error',
            'phpError',
        ];
    }

    /**
     * Set the template view.
     *
     * @param  string|null $template The template identifier.
     * @throws InvalidArgumentException If the template view is invalid.
     * @return HandlerConfig Chainable
     */
    public function setTemplate($template): static
    {
        if (empty($template)) {
            $this->template = null;
            return $this;
        }

        if (!is_string($template)) {
            throw new InvalidArgumentException(
                'Handler view template must be a string identifier.'
            );
        }

        $this->template = $template;
        return $this;
    }

    /**
     * Retrieve the template view.
     */
    public function template(): string
    {
        if ($this->template === null) {
            return 'charcoal/app/handler/layout';
        }

        return $this->template;
    }

    /**
     * Set the template controller.
     *
     * @param  string|null $controller Handler controller name.
     * @throws InvalidArgumentException If the template controller is invalid.
     */
    public function setController($controller): static
    {
        if (empty($controller)) {
            $this->controller = null;
            return $this;
        }

        if (!is_string($controller)) {
            throw new InvalidArgumentException(
                'Handler view controller must be a string.'
            );
        }

        $this->controller = $controller;
        return $this;
    }

    /**
     * Retrieve the template controller.
     *
     * @return string
     */
    public function controller()
    {
        if ($this->controller === null) {
            return $this->defaultController();
        }

        return $this->controller;
    }

    /**
     * Retrieve the default template controller.
     *
     * @return string
     */
    public function defaultController()
    {
        $config = App::instance()->config();

        if (isset($config['view'])) {
            $viewConfig = $config['view'];
            if (isset($viewConfig['default_controller'])) {
                return $viewConfig['default_controller'];
            }
        }

        return 'charcoal/app/template/generic';
    }

    /**
     * Set the template rendering engine.
     *
     * @param  string|null $engine The view engine identifier.
     * @throws InvalidArgumentException If the engine is invalid.
     */
    public function setEngine($engine): static
    {
        if (empty($engine)) {
            $this->engine = null;
            return $this;
        }

        if (!is_string($engine)) {
            throw new InvalidArgumentException(
                'Engine must be a string (the engine ident)'
            );
        }

        $this->engine = $engine;
        return $this;
    }

    /**
     * Retrieve the template rendering engine.
     *
     * @return string
     */
    public function engine()
    {
        if ($this->engine === null) {
            return $this->defaultEngine();
        }

        return $this->engine;
    }

    /**
     * Retrieve the default template rendering engine.
     *
     * @return string
     */
    public function defaultEngine()
    {
        $config = App::instance()->config();
        if (isset($config['view.default_engine'])) {
            return $config['view.default_engine'];
        } else {
            return 'mustache';
        }
    }

    /**
     * Set the "handlerMessage" view ident.
     *
     * @param  string $templateIdent A template identifier.
     */
    public function setPartial($templateIdent): static
    {
        $this->partials['handlerMessage'] = $templateIdent;
        return $this;
    }

    /**
     * Retrieve the "handlerMessage" view ident.
     *
     * @return string
     */
    public function partial()
    {
        return $this->partials['handlerMessage'];
    }

    /**
     * Set the dynamic template partials.
     *
     * @param  array $partials Dynamic templates.
     * @return HandlerConfig Chainable
     */
    public function setPartials(array $partials): static
    {
        $this->partials = array_replace($this->partials, $partials);
        return $this;
    }

    /**
     * Retrieve the dynamic template partials.
     *
     * @return string[]
     */
    public function partials()
    {
        return $this->partials;
    }

    /**
     * Set the template data for the view.
     *
     * @param  array $data Additional template data.
     * @return HandlerConfig Chainable
     */
    public function setTemplateData(array $data): static
    {
        $this->templateData = array_merge($this->templateData, $data);
        return $this;
    }

    /**
     * Retrieve the template data for the view.
     */
    public function templateData(): array
    {
        return $this->templateData;
    }

    /**
     * Enable/Disable the template cache.
     *
     * @param  boolean $cache The cache flag.
     * @return HandlerConfig Chainable
     */
    public function setCache($cache): static
    {
        $this->cache = (bool)$cache;
        return $this;
    }

    /**
     * Determine if the cache is enabled or disabled.
     */
    public function cache(): bool
    {
        return $this->cache;
    }

    /**
     * Set the time-to-live for the cached template.
     *
     * @param  integer $ttl The time-to-live, in seconds.
     * @return HandlerConfig Chainable
     */
    public function setCacheTtl($ttl): static
    {
        $this->cacheTtl = intval($ttl);
        return $this;
    }

    /**
     * Retrieve the time-to-live for the cached template.
     */
    public function cacheTtl(): int
    {
        return $this->cacheTtl;
    }
}
