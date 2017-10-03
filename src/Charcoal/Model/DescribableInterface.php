<?php

namespace Charcoal\Model;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataLoader;

/**
 * Defines a model having metadata that allows the customization of objects.
 */
interface DescribableInterface
{
    /**
     * @param array $data The object data.
     * @return DescribableInterface Chainable
     */
    public function setData(array $data);

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
}
