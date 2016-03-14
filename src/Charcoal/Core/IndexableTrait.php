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
    * @param mixed $id The object id (identifier / primary key value).
    * @throws InvalidArgumentException If the argument is not scalar.
    * @throws Exception If the key is invalid.
    * @return IndexableInterface Chainable
    */
    public function setId($id)
    {
        if (!is_scalar($id)) {
            throw new InvalidArgumentException(
                sprintf(
                    'ID must be a scalar (integer, float, string, or boolean); received %s',
                    (is_object($id) ? get_class($id) : gettype($id))
                )
            );
        }

        $key = $this->key();
        if ($key == 'id') {
            $this->id = $id;
        } else {
            $func = [ $this, $this->setter($key) ];
            if (is_callable($func)) {
                call_user_func($func, $id);
            } else {
                throw new Exception('Invalid Key');
            }
        }

        return $this;
    }

    /**
    * Get the object's (unique) ID. The actualy property get depends on `key()`
    *
    * @throws Exception If the set key is invalid.
    * @return mixed
    */
    public function id()
    {
        $key = $this->key();
        if ($key == 'id') {
            return $this->id;
        }

        $func = [ $this, $this->getter($key) ];
        if (is_callable($func)) {
            return call_user_func($func);
        } else {
            throw new Exception('Invalid Key');
        }
    }

    /**
    * Set the key property.
    *
    * @param string $key The object key, or identifier "name".
    * @throws InvalidArgumentException If the argument is not scalar.
    * @return IndexableInterface Chainable
    */
    public function setKey($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Key must be a string; received %s',
                    (is_object($key) ? get_class($key) : gettype($key))
                )
            );
        }
        // For security reason, only alphanumeric characters (+ underscores) are valid key names.
        // Although SQL can support more, there's really no reason to.
        if (!preg_match('/[A-Za-z0-9_]/', $key)) {
            throw new InvalidArgumentException(
                sprintf('Key "%s" is invalid: must be alphanumeric / underscore.', $key)
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
