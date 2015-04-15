<?php

namespace Charcoal\Metadata;

use \Charcoal\Metadata\MetadataInterface as MetadataInterface;

use \Charcoal\Property\PropertyInterface as PropertyInterface;

interface DescribableInterface
{
    /**
    * @param array|MetadataInterface $metadata
    * @return DescribableInterface Chainable
    */
    public function set_metadata($metadata);

    /**
    * @return MetadataInterface
    */
    public function metadata();
    
    /**
    * @param string $metadata_ident
    */
    public function load_metadata($metadata_ident=null);

    /**
    * @param string $metadata_ident
    * @return DescribableInterface Chainable
    */
    public function set_metadata_ident($metadata_ident);

    /**
    * @return string
    */
    public function metadata_ident();

    public function properties();
    public function property($property_ident);
    public function p($property_ident=null);
}
