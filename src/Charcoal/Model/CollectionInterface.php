<?php

namespace Charcoal\Model;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Model\ModelInterface;

/**
*
*/
interface CollectionInterface
{
    /**
    * Get the ordered object array
    *
    * @return array
    */
    public function objects();

    /**
    * Get the map array, with IDs as keys
    *
    * @return array
    */
    public function map();

    /**
    * Manually add an object to the list
    *
    * @param Charcoal_Base $obj
    *
    * @return \Charcoal\Collection (Chainable)
    */
    public function add(ModelInterface $obj);

    /**
    * @param string|ModelInterface $key
    * @throws InvalidArgumentException if the offset is not a string
    * @return integer|boolean
    */
    public function pos($key);
}
