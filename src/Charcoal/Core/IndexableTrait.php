<?php

namespace Charcoal\Core;

use \Exception;
use \InvalidArgumentException;

/**
* A default implementation, as trait, of the `IndexableInterface`.
*/
trait IndexableTrait
{
    /**
    * @var mixed $id The object (unique) identifier
    */
    protected $id;

    /**
    * @var string $key The object key
    */
    protected $key = 'id';

    /**
    * Set the object's ID. The actual property set depends on `key()`
    *
    * @param mixed $id
    * @throws InvalidArgumentException if the argument is not scalar
    * @throws Exception if the key is invalid
    * @return IndexableInterface Chainable
    */
    public function setId($id)
    {
        if (!is_scalar($id)) {
            throw new InvalidArgumentException(
                'ID argument must be a scalar (integer, float, string, or boolean).'
            );
        }

        $key = $this->key();
        if ($key == 'id') {
            $this->id = $id;
        } else {
            $func = [$this, 'set_'.$key];
            if (is_callable($func)) {
                call_user_func($func, $id);
            } else {
                throw new Exception('Invalid key.');
            }
        }

        return $this;
    }

    /**
    * Get the object's (unique) ID. The actualy property get depends on `key()`
    *
    * @throws Exception if the set key is invalid
    * @return mixed
    */
    public function id()
    {
        $key = $this->key();
        if ($key == 'id') {
            return $this->id;
        }

        $func = [$this, $key];
        if (is_callable($func)) {
            return call_user_func($func);
        } else {
            throw new Exception(
                'Invalid key.'
            );
        }
    }

    /**
    * Set the key property.
    *
    * @param string $key
    * @throws InvalidArgumentException if the argument is not scalar
    * @return IndexableInterface Chainable
    */
    public function setKey($key)
    {
        if (!is_scalar($key)) {
            throw new InvalidArgumentException(
                'Key argument must be scalar (integer, float, string, or boolean).'
            );
        }
        $this->key = $key;

        return $this;
    }

    /**
    * Get the key property.
    *
    * @return string
    */
    public function key()
    {
        return $this->key;
    }
}
