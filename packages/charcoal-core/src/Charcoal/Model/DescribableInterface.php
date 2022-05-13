<?php

namespace Charcoal\Model;

/**
 * Describable objects are defined by a metadata object.
 *
 * A describable object has what's needed to load its metadata.
 */
interface DescribableInterface
{
    /**
     * Describable objects must have a way to set their own data from an array.
     * Therefore, the `setData()` method must exist on the object.
     *
     * @param array $data The object data.
     * @return self
     */
    public function setData(array $data);

    /**
     * @param array|MetadataInterface $metadata The actual object metadata, either as an array or metadata object.
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
     * @param string $metadataIdent Explicitly set the metadata ident.
     * @return self
     */
    public function setMetadataIdent($metadataIdent);

    /**
     * @return string
     */
    public function metadataIdent();
}
