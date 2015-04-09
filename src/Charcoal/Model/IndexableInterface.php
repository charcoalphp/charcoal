<?php

namespace Charcoal\Model;

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
    *
    */
    public function id();
    public function set_key($key);
    public function key();
}
