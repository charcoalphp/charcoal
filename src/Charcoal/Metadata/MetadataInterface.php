<?php

namespace Charcoal\Metadata;

interface MetadataInterface
{
    /**
    * @return array
    */
    public function properties();

    /**
    * @param array
    * @return MetadataInterface Chainable
    */
    public function set_properties($properties);
}
