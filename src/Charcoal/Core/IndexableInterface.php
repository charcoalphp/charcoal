<?php

namespace Charcoal\Core;

/**
* Indexable interace (object that holds a unique id, defined by a key).
*/
interface IndexableInterface
{
    /**
    * Set the object's ID. The actual property set depends on `key()`
    *
    * @param mixed $id
    * @return IndexableInterface Chainable
    */
    public function set_id($id);

    /**
    * Get the object's (unique) ID. The actualy property get depends on `key()`
    *
    * @return mixed
    */
    public function id();

    /**
    * Set the key property.
    *
    * @param string $key
    * @return IndexableInterface Chainable
    */
    public function set_key($key);

    /**
    * Get the key property.
    *
    * @return string
    */
    public function key();
}
