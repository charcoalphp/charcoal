<?php

namespace Charcoal\View;

// Local namespace dependencies
use \Charcoal\View\ViewControllerInterface;

/**
* Model View\Controller
*/
abstract class AbstractViewController implements ViewControllerInterface
{
    /**
    * @var mixed $context
    */
    protected $context;

    /**
    * @param mixed|null $context
    */
    public function __construct($context = null)
    {
        if ($context !== null) {
            $this->set_context($context);
        }
    }

    /**
    * The Model View\Controller is a decorator around the Model.
    *
    * Because of (Mustache) template engine limitation, this also check for methods
    * because `__call()` can not be used.
    *
    * @param string $name
    *
    * @return mixed
    * @see    https:// github.com/bobthecow/mustache.php/wiki/Magic-Methods
    */
    public function __get($name)
    {
        $context = $this->context();
        if ($context === null) {
            return null;
        }

        if (is_object($context)) {
            // Try methods
            if (is_callable([$context, $name])) {
                return call_user_func([$context, $name]);
            }
            // Try Properties
            if (isset($context->{$name})) {
                return $context->{$name};
            }
        } elseif (is_array($context)) {
            if (isset($context[$name])) {
                return $context[$name];
            }
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
        $context = $this->context();
        if ($context === null) {
            return null;
        }

        if (is_object($context)) {
            if (is_callable([$context, $name])) {
                return call_user_func_array([$context, $name], $arguments);
            }
            // Try Properties
            if (isset($context->{$name})) {
                return $context->{$name};
            }
        } elseif (is_array($context)) {
            if (isset($context[$name])) {
                return $context[$name];
            }
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
        $context = $this->context();
        if ($context === null) {
            return false;
        }

        if (is_object($context)) {
            // Try methods
            if (is_callable([$context, $name])) {
                return true;
            }

            // Try Properties
            if (isset($context->{$name})) {
                return true;
            }
        } elseif (is_array($context)) {
            if (isset($context[$name])) {
                return $context[$name];
            }
        }
        return false;
    }

    /**
    * @param mixed $context
    * @return ViewControllerInterface Chainable
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
}
