<?php

namespace Charcoal\Core;

/**
* A default implementation, as trait, of the `IndexableInterface`.
*/
trait IndexableTrait
{
    /**
    * @var mixed $_id The object (unique) identifier
    */
    public $_id;
    /**
    * @var string $_key The object key
    */
    public $_key;

    /**
    * Set the object's ID. The actual property set depends on `key()`
    *
    * @param mixed $id
    * @throws \InvalidArgumentException if the argument is not scalar
    * @return IndexableInterface Chainable
    */
    public function set_id($id)
    {
        if (!is_scalar($id)) {
            throw new \InvalidArgumentException('Id argument must be scalar');
        }

        $key = $this->key();
        if ($key == 'id') {
            $this->_id = $id;
        } else {
            $func = [$this, 'set_'.$key];
            if (is_callable($func)) {
                call_user_func($func, $id);
            } else {
                $this->{$key} = $id;
            }
        }

        return $this;
    }

    /**
    * Get the object's (unique) ID. The actualy property get depends on `key()`
    *
    * @throws \Exception if the set key is invalid
    * @return mixed
    */
    public function id()
    {
        $key = $this->key();
        if ($key == 'id') {
            return $this->_id;
        }

        $func = [$this, $key];
        if (is_callable($func)) {
            return call_user_func($func);
        } else {
            throw new \Exception('Invalid key');
        }
    }

    /**
    * Set the key property.
    *
    * @param string $key
    * @throws \InvalidArgumentException if the argument is not scalar
    * @return IndexableInterface Chainable
    */
    public function set_key($key)
    {
        if (!is_scalar($key)) {
            throw new \InvalidArgumentException('Key argument must be scalar');
        }
        $this->_key = $key;

        return $this;
    }

    /**
    * Get the key property.
    *
    * @return string
    */
    public function key()
    {
        if ($this->_key === null) {
            $this->_key = 'id';
        }
        return $this->_key;
    }
}
