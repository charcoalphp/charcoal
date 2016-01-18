<?php

namespace Charcoal\Model;

/**
* Model Interface
*/
interface ModelInterface
{
    /**
    * @param array $data
    * @return ModelInterface Chainable
    */
    public function setData(array $data);

    /**
    * @return array
    */
    public function data();

    /**
    * @param array $data
    * @return ModelInterface Chainable
    */
    public function setFlatData(array $data);

    /**
    * @return array
    */
    public function flatData();

    /**
    * @return array
    */
    public function properties();

    /**
    * @param string $property_ident
    * @return PropertyInterface
    */
    public function property($property_ident);

    /**
    * Alias of `properties()` (if not parameter is set) or `property()`.
    *
    * @param string $property_ident
    * @return mixed
    */
    public function p($property_ident = null);
}
