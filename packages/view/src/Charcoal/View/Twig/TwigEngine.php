<?php

declare(strict_types=1);

namespace Charcoal\View\Twig;

use Charcoal\View\AbstractEngine;
use Charcoal\View\ViewConfig;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader as TwigFilesystemLoader;

/**
 *
 */
class TwigEngine extends AbstractEngine
{
    public const DEFAULT_CACHE_PATH = '../cache/twig';

    /**
     * @var TwigEnvironment $twig
     */
    private $twig;

    /**
     * @var ViewConfig $config
     */
    private $config;

    /**
     * @var boolean $debug
     */
    private $debug;

    /**
     * @return string
     */
    public function type(): string
    {
        return 'twig';
    }

    /**
     * Build the Twig Engine with an array of dependencies.
     *
     * @param array $data Engine dependencies.
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->setConfig($data['config']);
        $this->debug = $data['debug'];

        if (isset($data['helpers'])) {
            $this->setHelpers($data['helpers']);
        }
    }

    /**
     * Set the engine's helpers.
     *
     * @param  array|Traversable|HelpersInterface $helpers Twig helpers.
     * @throws InvalidArgumentException If the given helper(s) are invalid.
     * @return TwigEngine Chainable
     */
    public function setHelpers($helpers)
    {
        if ($helpers instanceof HelpersInterface) {
            $helpers = $helpers->toArray();
        }

        if (!is_iterable($helpers)) {
            throw new InvalidArgumentException(sprintf(
                'setHelpers expects an array of helpers, received %s',
                (is_object($helpers) ? get_class($helpers) : gettype($helpers))
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
     * @param  array|Traversable|HelpersInterface $helpers Twig helpers.
     * @throws InvalidArgumentException If the given helper(s) are invalid.
     * @return TwigEngine Chainable
     */
    public function mergeHelpers($helpers)
    {
        if ($helpers instanceof HelpersInterface) {
            $helpers = $helpers->toArray();
        }

        if (!is_iterable($helpers)) {
            throw new InvalidArgumentException(sprintf(
                'mergeHelpers expects an array of helpers, received %s',
                (is_object($helpers) ? get_class($helpers) : gettype($helpers))
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
     * @throws RuntimeException If the twig engine was already initialized.
     * @return TwigEngine Chainable
     */
    public function addHelper($name, $helper)
    {
        if ($this->twig !== null) {
            throw new RuntimeException(
                'Can not add helper to Twig engine: the engine has already been initialized.'
            );
        }

        $this->helpers[$name] = $helper;

        return $this;
    }

    /**
     * Retrieve the engine's helpers.
     *
     * @return array
     */
    public function helpers(): ?array
    {
        return $this->helpers;
    }

    /**
     * @return Twig_Environment
     */
    public function twig(): TwigEnvironment
    {
        if ($this->twig === null) {
            $this->twig = $this->createTwig();
        }
        return $this->twig;
    }

    /**
     * @param string $templateIdent The template identifier to load and render.
     * @param mixed  $context       The rendering context.
     * @return string The rendered template string.
     */
    public function render(string $templateIdent, $context): string
    {
        $arrayContext = json_decode(json_encode($context), true);
        return $this->twig()->render($templateIdent, $arrayContext);
    }

    /**
     * @param string $templateString The template string to render.
     * @param mixed  $context        The rendering context.
     * @return string The rendered template string.
     */
    public function renderTemplate(string $templateString, $context): string
    {
        $template = $this->twig()->createTemplate($templateString);
        $arrayContext = json_decode(json_encode($context), true);
        return $template->render($arrayContext);
    }

    /**
     * @return Twig_Environment
     */
    protected function createTwig(): TwigEnvironment
    {
        $useCache = ($this->config()->useCache ?? true);
        $twig = new TwigEnvironment($this->loader(), [
            'cache'             => ($useCache ? $this->cache() : false),
            'charset'           => 'utf-8',
            'auto_reload'       => false,
            'strict_variables'  => ($this->config()->strictVariables ?? true),
            'debug'             => $this->debug,
        ]);
        $twig->setExtensions($this->helpers());
        return $twig;
    }

    /**
     * Set the engine's cache implementation.
     *
     * @param  mixed $cache A Twig cache option.
     * @return void
     */
    protected function setCache($cache): void
    {
        /**
         * If NULL is specified, the value is converted to FALSE
         * because Twig internally requires FALSE to disable the cache.
         */
        if ($cache === null) {
            $cache = false;
        }

        parent::setCache($cache);
    }

    public function setConfig(ViewConfig $config): void
    {
        $this->config = $config;
    }

    public function config(): ViewConfig
    {
        return $this->config;
    }
}
