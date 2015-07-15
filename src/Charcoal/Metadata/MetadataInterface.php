<?php

namespace Charcoal\Metadata;

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
