<?php

namespace Charcoal\Model;

use \Charcoal\Model\Model as Model;

/**
*
*/
class Source
{
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
