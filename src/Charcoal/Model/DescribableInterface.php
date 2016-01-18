<?php

namespace Charcoal\Model;

// Local namespace dependencies
use \Charcoal\Model\MetadataInterface;

/**
*
*/
interface DescribableInterface
{
    /**
    * @param array $data
    * @return DescribableInterface Chainable
    */
    public function setData(array $data);

    /**
    * @param array|MetadataInterface $metadata
    * @return DescribableInterface Chainable
    */
    public function setMetadata($metadata);

    /**
    * @return MetadataInterface
    */
    public function metadata();

    /**
    * @param string $metadataIdent
    * @return MetadataInterface
    */
    public function loadMetadata($metadataIdent = null);

    /**
    * @param string $metadataIdent
    * @return DescribableInterface Chainable
    */
    public function setMetadataIdent($metadataIdent);

    /**
    * @return string
    */
    public function metadataIdent();

    /**
    * Get the list of properties, as array of `PropertyInterface`
    *
    * @return array
    */
    public function properties();

    /**
    * Get a single property
    *
    * @param string $propertyIdent
    * @return PropertyInterface
    */
    public function property($propertyIdent);

    /**
    * Alias of `property()` or `properties()`, depending if argument is set or not.
    *
    * @param mixed $propertyIdent Property ident, if null, return all properties
    * @return array|PropertyInterface|null
    */
    public function p($propertyIdent = null);

    /**
    * @param array $filters The filters to apply
    * @return boolean False if the object doesn't match any filter, true otherwise.
    */
    public function isFiltered(array $filters = null);
}
