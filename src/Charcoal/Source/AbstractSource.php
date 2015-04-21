<?php

namespace Charcoal\Source;

use \Charcoal\Config\ConfigurableInterface as ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait as ConfigurableTrait;

use \Charcoal\Source\SourceInterface as SourceInterface;

abstract class AbstractSource implements
    SourceInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;

    private $_model = null;

    /**
    * @var Model $models
    * @return Source Chainable
    */
    public function set_model(Model $model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
    * @throws \Exception if not model was previously set
    * @return Model
    */
    public function model()
    {
        if ($this->_model === null) {
            throw new \Exception('No model set.');
        }
        return $this->_model;
    }
}
