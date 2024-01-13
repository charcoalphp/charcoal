<?php

namespace Charcoal\Admin\Action\System;

// From PSR-6
use Psr\Cache\CacheItemPoolInterface;
// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From Pimple
use Pimple\Container;
// From 'charcoal-cache'
use Charcoal\Cache\CachePoolAwareTrait;
// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\View\EngineInterface;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\Twig\TwigEngine;

/**
 * Base Cache Action
 */
abstract class AbstractCacheAction extends AdminAction
{
    use CachePoolAwareTrait;

    /**
     * Mustache View Engine.
     *
     * @var MustacheEngine|(callable():?MustacheEngine)
     */
    private $mustacheEngine;

    /**
     * Twig View Engine.
     *
     * @var TwigEngine|(callable():?TwigEngine)
     */
    private $twigEngine;

    public function getMustacheEngine(): ?MustacheEngine
    {
        if (is_callable($this->mustacheEngine)) {
            $this->mustacheEngine = ($this->mustacheEngine)();
        }

        if ($this->mustacheEngine instanceof MustacheEngine) {
            return $this->mustacheEngine;
        }

        return null;
    }

    public function getTwigEngine(): ?TwigEngine
    {
        if (is_callable($this->twigEngine)) {
            $this->twigEngine = ($this->twigEngine)();
        }

        if ($this->twigEngine instanceof TwigEngine) {
            return $this->twigEngine;
        }

        return null;
    }

    /**
     * @return array
     */
    public function results()
    {
        return [
            'success'   => $this->success(),
            'feedbacks' => $this->feedbacks(),
        ];
    }

    /**
     * Set dependencies from the service locator.
     *
     * @param  Container $container A service locator.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setCachePool($container['cache']);

        $this->mustacheEngine = function () use ($container) {
            if (class_exists('\Mustache_Engine')) {
                return $container['view/engine/mustache'];
            }

            return null;
        };
        $this->twigEngine = function () use ($container) {
            if (class_exists('\Twig\Environment')) {
                return $container['view/engine/twig'];
            }

            return null;
        };
    }
}
