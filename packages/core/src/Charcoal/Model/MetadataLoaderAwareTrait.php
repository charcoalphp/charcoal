<?php

namespace Charcoal\Model;

use RuntimeException;
use Charcoal\Model\Service\MetadataLoader;

/**
 * Provides describable metadata features.
 *
 * Metadata Loader Aware Trait
 * @package Charcoal\Model
 */
trait MetadataLoaderAwareTrait
{
    /**
     * Store the metadata loader.
     *
     * @var MetadataLoader
     */
    protected $metadataLoader;

    /**
     * Set a metadata loader.
     *
     * @param  MetadataLoader $loader The metadata loader.
     * @return void
     */
    protected function setMetadataLoader(MetadataLoader $loader)
    {
        $this->metadataLoader = $loader;
    }

    /**
     * Retrieve the metadata loader.
     *
     * @throws RuntimeException If the metadata loader is missing.
     * @return MetadataLoader
     */
    public function metadataLoader()
    {
        if (!isset($this->metadataLoader)) {
            throw new RuntimeException(sprintf(
                'Metadata Loader is not defined for [%s]',
                get_class($this)
            ));
        }

        return $this->metadataLoader;
    }

    /**
     * Load a metadata file.
     *
     * @param  string $metadataIdent A metadata file path or namespace.
     * @return MetadataInterface
     */
    protected function loadMetadata($metadataIdent)
    {
        $metadataLoader = $this->metadataLoader();
        $metadata = $metadataLoader->load($metadataIdent, $this->createMetadata());

        return $metadata;
    }

    /**
     * Retrieve a new metadata object.
     *
     * @return MetadataInterface
     */
    abstract protected function createMetadata();
}
