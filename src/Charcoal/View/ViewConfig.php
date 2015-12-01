<?php

namespace Charcoal\View;

use \InvalidArgumentException;

// Module `charcoal-config` dependencies
use \Charcoal\Config\AbstractConfig;

/**
 * View configuration.
 */
class ViewConfig extends AbstractConfig
{
    /**
     * @var array $template_path
     */
    private $template_path = [];

    /**
     * @var array $engines
     */
    private $engines = [];

    /**
     * @var string $default_engine
     */
    private $default_engine;

    /**
     * @return array
     */
    public function default_data()
    {
        return [
            'template_path' => [],
            'engines' => [
                'mustache' => [

                ],
                'php' => [

                ],
                'php-mustache' => [

                ]
            ],
            'default_engine'=> 'mustache'
        ];
    }

    /**
     * @param array $path
     * @return ViewConfig Chainable
     */
    public function set_template_path(array $path)
    {
        $this->template_path = [];
        foreach ($path as $p) {
            $this->add_template_path($p);
        }
        return $this;
    }

    /**
     * @param array $path
     * @throws InvalidArgumentException
     * @return ViewConfig Chainable
     */
    public function add_template_path($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Template path must be a string'
            );
        }
        $this->template_path[] = $path;
        return $this;
    }

    /**
     * @return array
     */
    public function template_path()
    {
        return $this->template_path;
    }

    /**
     * @param array $engines
     * @return ViewConfig Chainable
     */
    public function set_engines($engines)
    {
        $this->engines = [];
        foreach ($engines as $engine_ident => $engine_config) {
            $this->add_engine($engine_ident, $engine_config);
        }
        return $this;
    }

    /**
     * @param string $engine_ident
     * @param array  $engine_config
     * @return ViewConfig Chainable
     */
    public function add_engine($engine_ident, $engine_config)
    {
        $this->engines[$engine_ident] = $engine_config;
        return $this;
    }

    /**
     * @return array
     */
    public function engines()
    {
        return $this->engines;
    }

    /**
     * @param string|null $engine_ident
     * @throws InvalidArgumentException
     * @return array
     */
    public function engine($engine_ident = null)
    {
        if ($engine_ident === null) {
            $engine_ident = $this->default_engine();
        }
        if (!is_string($engine_ident)) {
            throw new InvalidArgumentException(
                'Invalid engine identifier (must be a string)'
            );
        }

        if (!isset($this->engines[$engine_ident])) {
            throw new InvalidArgumentException(
                sprintf('No configured engines matching "%s"', $engine_ident)
            );
        }
        return $this->engines[$engine_ident];
    }

    /**
     * @param string $engine
     * @throws InvalidArgumentException
     * @return ViewConfig Chainable
     */
    public function set_default_engine($engine)
    {
        if (!is_string($engine)) {
            throw new InvalidArgumentException(
                'Default engine must be a string'
            );
        }
        $this->default_engine = $engine;
        return $this;
    }

    /**
     * @return string
     */
    public function default_engine()
    {
        return $this->default_engine;
    }
}
