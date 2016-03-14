<?php

namespace Charcoal\Model;

// Module (`charcoal-property`) dependencies
use \Charcoal\Property\PropertyFactory;

// Local namespace dependencies
use \Charcoal\Model\MetadataLoader;
use \Charcoal\Model\MetadataInterface;

/**
*
*/
interface DescribableInterface
{
    /**
    * @param array|\ArrayAccess $data The object data.
    * @return DescribableInterface Chainable
    */
    public function setData($data);

    /**
     * @param PropertyFactory $factory The property factory, used to create metadata properties.
     * @return DescribableInterface Chainable
     */
    public function setPropertyFactory(PropertyFactory $factory);

    /**
     * @param MetadataLoader $loader The loader instance, used to load metadata.
     * @return DescribableInterface Chainable
     */
    public function setMetadataLoader(MetadataLoader $loader);

    /**
    * @param array|MetadataInterface $metadata The matadata.
    * @return DescribableInterface Chainable
    */
    public function setMetadata($metadata);

    /**
    * @return MetadataInterface
    */
    public function metadata();

    /**
    * @param string $metadataIdent The metadata ident to load. If null, generate from object.
    * @return MetadataInterface
    */
    public function loadMetadata($metadataIdent = null);

    /**
    * @param string $metadataIdent Explicitely set the metadata ident.
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
    * @param string $propertyIdent The ident of the property to get.
    * @return PropertyInterface
    */
    public function property($propertyIdent);

    /**
    * Alias of `property()` or `properties()`, depending if argument is set or not.
    *
    * @param mixed $propertyIdent Property ident, if null, return all properties.
    * @return array|PropertyInterface|null
    */
    public function p($propertyIdent = null);

    /**
    * @param array $filters The filters to apply.
    * @return boolean False if the object doesn't match any filter, true otherwise.
    */
    public function isFiltered(array $filters = null);
}
