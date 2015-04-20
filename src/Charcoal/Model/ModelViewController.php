<?php

namespace Charcoal\Model;

use \Charcoal\View\AbstractViewController as AbstractViewController;
use \Charcoal\Model\ModelInterface as ModelInterface;

/**
* Model ViewController
*/
class ModelViewController extends AbstractViewController
{
    /**
    * The Model View\Controller is a decorator around the Model.
    *
    * Because of (Mustache) template engine limitation, this also check for methods
    * because `__call()` can not be used.
    *
    * @param string $name
    *
    * @return mixed
    * @see    https://github.com/bobthecow/mustache.php/wiki/Magic-Methods
    */
    public function __get($name)
    {

        $model = $this->_model();
        if ($model === null) {
            return null;
        }

        // Try methods
        if (is_callable([$model, $name])) {
            return call_user_func([$model, $name]);
        }
        // Try Properties
        if (isset($model->{$name})) {
            return $model->{$name};
        }
        return null;
    }

    /**
    * The Model View\Controller is a decorator around the Model
    *
    * @param string $name
    * @param mixed  $arguments
    *
    * @return mixed
    */
    public function __call($name, $arguments)
    {
        $model = $this->_model();
        if ($model === null) {
            return null;
        }

        if (is_callable([$model, $name])) {
            return call_user_func_array([$model, $name], $arguments);
        }

        return null;
    }
    
    /**
    * @param string $name
    *
    * @return boolean
    */
    public function __isset($name)
    {
        $model = $this->_model();
        if ($model === null) {
            return false;
        }

        // Try methods
        if (is_callable([$model, $name])) {
            return true;
        }

        // Try Properties
        if (isset($model->{$name})) {
            return true;
        }
        return false;
    }

    /**
    *
    */
    public function set_context($context)
    {
        $this->_context = $context;
    }

    /**
    * @throws \Exception if the context is not a model
    */
    protected function _model()
    {
        if ($this->_context === null) {
            return null;
        }
        if (!($this->_context instanceof ModelInterface)) {
            throw new \Exception('Context neeeds to be a Model');
        }
        return $this->_context;
    }

}
