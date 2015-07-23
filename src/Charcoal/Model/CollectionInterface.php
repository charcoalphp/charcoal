<?php

namespace Charcoal\Model;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Core\IndexableInterface as IndexableInterface;

/**
* @todo Implement CollectionInterface.
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
    public function add(IndexableInterface $obj);

    /**
    * @param string|IndexableInterface $key
    * @throws InvalidArgumentException if the offset is not a string
    * @return integer|boolean
    */
    public function pos($key);
}
