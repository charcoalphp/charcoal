<?php

namespace Charcoal\View;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Module `charcoal-config` dependencies
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\View\Mustache\MustacheEngine;
use \Charcoal\View\Php\PhpEngine;
use \Charcoal\View\PhpMustache\PhpMustacheEngine;
use \Charcoal\View\Twig\TwigEngine;
use \Charcoal\View\ViewInterface;

/**
 * Base abstract class for _View_ interfaces, implements `ViewInterface`.
 *
 * Also implements the `ConfigurableInterface`
 */
abstract class AbstractView implements
    ConfigurableInterface,
    LoggerAwareInterface,
    ViewInterface
{
    use LoggerAwareTrait;
    use ConfigurableTrait;

    const DEFAULT_ENGINE = 'mustache';

    /**
     * @var string $templateIdent
     */
    private $templateIdent;

    /**
     * @var string $template
     */
    private $template;

    /**
     * @var string $engineType
     */
    private $engineType = self::DEFAULT_ENGINE;

    /**
     * @var EngineInterface $engine
     */
    private $engine;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->renderTemplate();
    }

    /**
     * @param array $data
     * @return AbstractView Chainable
     */
    public function setData(array $data)
    {
        foreach ($data as $prop => $val) {
            $setter = $this->setter($prop);
            $func = [$this, $setter];
            if (is_callable($func)) {
                call_user_func($func, $val);
            } else {
                $this->{$prop} = $val;
            }
        }

        return $this;
    }

    /**
     * > ConfigurableTrait . createConfig()
     *
     * @param array $data
     * @return ViewConfig
     */
    public function createConfig(array $data = null)
    {
        $config = new ViewConfig();
        if ($data !== null) {
            $config->merge($data);
        }
        return $config;
    }

    /**
     * Set the engine type
     *
     * @param string $engineType
     * @throws InvalidArgumentException
     * @return AbstractView Chainable
     */
    public function setEngineType($engineType)
    {
        if (!is_string($engineType)) {
            throw new InvalidArgumentException(
                'Engine type must be a string (mustache, php or php-mustache)'
            );
        }
        $this->engineType = $engineType;
        return $this;
    }

    /**
     * @return string
     */
    public function engineType()
    {
        return $this->engineType;
    }

    /**
     * @param EngineInterface $engine
     */
    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * @return EngineInterface
     */
    public function engine()
    {
        if ($this->engine === null) {
            $this->engine = $this->createEngine();
        }
        return $this->engine;
    }

    /**
     * @return EngineInterface
     */
    public function createEngine()
    {
        $type = $this->engineType();
        switch ($type) {
            case 'mustache':
                return new MustacheEngine([
                    'logger' => $this->logger,
                    'cache'  => null,
                    'loader' => null
                ]);

                case 'php':
                return new PhpEngine([
                    'logger' => $this->logger,
                    'cache'  => null,
                    'loader' => null
                ]);

                case 'php-mustache':
                return new PhpMustacheEngine([
                    'logger' => $this->logger,
                    'cache'  => null,
                    'loader' => null
                ]);

                case 'twig':
                return new TwigEngine([
                    'logger' => $this->logger,
                    'cache'  => null,
                    'loader' => null
                ]);

                default:
                return new MustacheEngine([
                    'logger' => $this->logger,
                    'cache'  => null,
                    'loader' => null
                ]);
        }
    }

    /**
     * @param string $templateIdent
     * @throws InvalidArgumentException if the provided argument is not a string
     * @return AbstractView Chainable
     */
    public function setTemplateIdent($templateIdent)
    {
        if (!is_string($templateIdent)) {
            throw new InvalidArgumentException(
                'Template ident must be a string.'
            );
        }

        $this->templateIdent = $templateIdent;
        return $this;
    }

    /**
     * @return string
     */
    public function templateIdent()
    {
        return $this->templateIdent;
    }

    /**
     * @param string $template
     * @throws InvalidArgumentException if the provided argument is not a string
     * @return AbstractView Chainable
     */
    public function setTemplate($template)
    {
        if (!is_string($template)) {
            throw new InvalidArgumentException(
                'Template must be a string.'
            );
        }

        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function template()
    {
        if ($this->template === null) {
            return $this->loadTemplate();
        }

        return $this->template;
    }

    /**
     * @param string $templateIdent
     * @throws InvalidArgumentException
     * @return string
     */
    public function loadTemplate($templateIdent = null)
    {
        if ($templateIdent === null) {
            $templateIdent = $this->templateIdent();
        }
        if (!is_string($templateIdent)) {
            throw new InvalidArgumentException(
                'Template ident must be a string'
            );
        }
        if (!$templateIdent) {
            return '';
        }
        $template = $this->engine()->loadTemplate($templateIdent);
        return $template;
    }

    /**
     * @param mixed $context
     * @return AbstractView Chainable
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return mixed
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * @param string $template
     * @param mixed  $context
     * @return string The rendered template
     */
    public function render($templateIdent = null, $context = null)
    {
        if ($templateIdent === null) {
            $templateIdent = $this->templateIdent();
        }
        if ($context === null) {
            $context = $this->context();
        }
        return $this->engine()->render($templateIdent, $context);
    }

    /**
     * @param string $templateIdent
     * @param mixed  $context
     * @return string The rendered template
     */
    public function renderTemplate($template_string = null, $context = null)
    {
        if ($template_string === null) {
            $template_string = $this->template();
        }
        if ($context === null) {
            $context = $this->context();
        }
        return $this->engine()->render($template_string, $context);
    }

    /**
     * Allow an object to define how the key getter are called.
     *
     * @param string $key The key to get the getter from.
     * @param string $case Optional. The type of case to return. camel, pascal or snake.
     * @return string The getter method name, for a given key.
     */
    protected function getter($key, $case = 'camel')
    {
        $getter = $key;

        if ($case == 'camel') {
            return $this->camelize($getter);
        } elseif ($case == 'pascal') {
            return $this->pascalize($getter);
        } else {
            return $getter;
        }
    }

    /**
     * Allow an object to define how the key setter are called.
     *
     * @param string $key The key to get the setter from.
     * @param string $case Optional. The type of case to return. camel, pascal or snake.
     * @return string The setter method name, for a given key.
     */
    protected function setter($key, $case = 'camel')
    {
        $setter = 'set_'.$key;

        if ($case == 'camel') {
            return $this->camelize($setter);
        } elseif ($case == 'pascal') {
            return $this->pascalize($setter);
        } else {
            return $setter;
        }
    }

    /**
     * Transform a snake_case string to camelCase.
     *
     * @param string $str The snake_case string to camelize.
     * @return string The camelCase string.
     */
    private function camelize($str)
    {
        return lcfirst($this->pascalize($str));
    }

    /**
     * Transform a snake_case string to PamelCase.
     *
     * @param string $str The snake_case string to pascalize.
     * @return string The PamelCase string.
     */
    private function pascalize($str)
    {
        return implode('', array_map('ucfirst', explode('_', $str)));
    }
}
