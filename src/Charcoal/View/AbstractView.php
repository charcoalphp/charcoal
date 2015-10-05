<?php

namespace Charcoal\View;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Module `charcoal-config` dependencies
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\View\Mustache\MustacheEngine;
use \Charcoal\View\Php\PhpEngine;
use \Charcoal\View\PhpMustache\PhpMustacheEngine;
use \Charcoal\View\ViewInterface;

/**
* An abstract class that fulfills the full ViewInterface.
*
* There are 2 remaining abstract methods:
* - `load_template()`
* - `load_context()`
*/
abstract class AbstractView implements
    ConfigurableInterface,
    ViewInterface
{
    use ConfigurableTrait;

    const DEFAULT_ENGINE = 'mustache';

    /**
    * @var string $template_ident
    */
    private $template_ident;

    /**
    * @var string $template
    */
    private $template;

    /**
    * @var \Psr\Log\LoggerInterface $logger
    */
    // private $logger;

    /**
    * @var string $engine_type
    */
    private $engine_type = self::DEFAULT_ENGINE;

    /**
    * @var EngineInterface $engine
    */
    private $engine;



    /**
    * Build the object with an array of options.
    *
    * ## Required parameters:
    * - `config` a ViewConfig object
    * - `logger` a PSR logger
    *
    * @param array $data
    * @throws InvalidArgumentException If required parameters are missing.
    */
    public function __construct($data)
    {
        // Required parameters
        // if(!isset($data['logger'])) {
        //     throw new InvalidArgumentException(
        //         'Logger is required for the view constructor'
        //     );
        // }

        // set_config() is defined in the `ConfigurableTrait`
        if (isset($data['config'])) {
            $this->set_config($data['config']);
        }

        if (isset($data['logger'])) {
            $this->logger = $data['logger'];
        }
    }

    /**
    * @param array $data
    * @return AbstractView Chainable
    */
    public function set_data(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, 'set_'.$prop];
            if (is_callable($func)) {
                call_user_func($func, $val);
                unset($data[$prop]);
            } else {
                $this->{$prop} = $val;
            }
        }

        return $this;
    }

    /**
    * @return string
    */
    public function __toString()
    {
        return $this->render();
    }

    /**
    * > ConfigurableTrait . create_config()
    *
    * @param array $data
    * @return ViewConfig
    */
    public function create_config($data = null)
    {
        $config = new ViewConfig($data);
        return $config;
    }

    /**
    * Set the engine type
    *
    * @param string $engine_type
    * @throws InvalidArgumentException
    * @return AbstractView Chainable
    */
    public function set_engine_type($engine_type)
    {
        if (!is_string($engine_type)) {
            throw new InvalidArgumentException(
                'Engine type must be a string (mustache, php or php-mustache)'
            );
        }
        $this->engine_type = $engine_type;
        return $this;
    }

    /**
    * @return string
    */
    public function engine_type()
    {
        return $this->engine_type;
    }

    /**
    * @param EngineInterface $engine
    */
    public function set_engine(EngineInterface $engine)
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
            $this->engine = $this->create_engine();
        }
        return $this->engine;
    }

    /**
    * @return EngineInterface
    */
    public function create_engine()
    {
        $type = $this->engine_type();
        switch ($type) {
            case 'mustache':
                return new MustacheEngine([
                    'logger'=>null,
                    'cache'=>null,
                    'loader'=>null
                ]);

            case 'php':
                return new PhpEngine([
                    'logger'=>null,
                    'cache'=>null,
                    'loader'=>null
                ]);

            case 'php-mustache':
                return new PhpMustacheEngine([
                    'logger'=>null,
                    'cache'=>null,
                    'loader'=>null
                ]);

            default:
                return new MustacheEngine([
                    'logger'=>null,
                    'cache'=>null,
                    'loader'=>null
                ]);
        }
    }

    /**
    * @param string $template_ident
    * @throws InvalidArgumentException if the provided argument is not a string
    * @return AbstractView Chainable
    */
    public function set_template_ident($template_ident)
    {
        if (!is_string($template_ident)) {
            throw new InvalidArgumentException(
                'Template ident must be a string.'
            );
        }

        $this->template_ident = $template_ident;
        return $this;
    }

    /**
    * @return string
    */
    public function template_ident()
    {
        return $this->template_ident;
    }

    /**
    * @param string $template
    * @throws InvalidArgumentException if the provided argument is not a string
    * @return AbstractView Chainable
    */
    public function set_template($template)
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
            return $this->load_template();
        }

        return $this->template;
    }

    /**
    * @param string $template_ident
    * @throws InvalidArgumentException
    * @return string
    */
    public function load_template($template_ident = null)
    {
        if ($template_ident === null) {
            $template_ident = $this->template_ident();
        }
        if (!is_string($template_ident)) {
            throw new InvalidArgumentException(
                'Template ident must be a string'
            );
        }
        if (!$template_ident) {
            return '';
        }
        $template = $this->engine()->load_template($template_ident);
        return $template;
    }

    /**
    * @param mixed $context
    * @return AbstractView Chainable
    */
    public function set_context($context)
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
    public function render($template = null, $context = null)
    {
        if ($template === null) {
            $template = $this->template();
        }
        if ($context === null) {
            $context = $this->context();
        }

        return $this->engine()->render($template, $context);
    }

    /**
    * @param string $template_ident
    * @param mixed $context
    * @return string The rendered template
    */
    public function render_template($template_ident, $context = null)
    {
        $template = $this->load_template($template_ident);
        return $this->engine()->render($template, $context);
    }
}
