<?php

namespace Charcoal\Metadata;

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
    * @param array $properties
    * @return MetadataInterface Chainable
    */
    public function set_properties(array $properties);
}
