<?php

namespace Charcoal\Model;

/**
* Metadata is typically used to describe an object.
*
* Metadata
*/
interface MetadataInterface
{
    /**
    * @return array
    */
    public function properties();

    /**
    * @param array $properties The properties.
    * @return MetadataInterface Chainable
    */
    public function setProperties(array $properties);
}
