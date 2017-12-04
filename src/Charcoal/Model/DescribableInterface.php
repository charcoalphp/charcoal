<?php

namespace Charcoal\Model;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataLoader;

/**
 * Describable objects are defined by a metadata object.
 *
 * A describable object has what's needed to load its metadata.
 */
interface DescribableInterface
{
    /**
     * @param array $data The object data.
     * @return self
     */
    public function setData(array $data);

    /**
     * @param MetadataLoader $loader The loader instance, used to load metadata.
     * @return self
     */
    public function setMetadataLoader(MetadataLoader $loader);

    /**
     * @param array|MetadataInterface $metadata The matadata.
     * @return self
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
     * @return self
     */
    public function setMetadataIdent($metadataIdent);

    /**
     * @return string
     */
    public function metadataIdent();
}
