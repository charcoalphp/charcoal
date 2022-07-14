<?php

namespace Charcoal\Model;

use RuntimeException;
use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Model\MetadataInterface;
use Charcoal\Model\Service\MetadataLoader;

/**
 * Default implementation, as trait, of the `DescribableInterface`.
 */
trait DescribableTrait
{
    /**
     * @var MetadataLoader $metadataLoader
     */
    protected $metadataLoader = null;

    /**
     * @var MetadataInterface $metadata
     */
    protected $metadata;

    /**
     * @var string $metadataIdent
     */
    protected $metadataIdent;

    /**
     * Describable object needs to have a `setData()` method to allow a global setter from array.
     *
     * @param array $data The object's data.
     * @return self
     */
    abstract public function setData(array $data);


    /**
     * @param array|MetadataInterface $metadata The object's metadata.
     * @throws InvalidArgumentException If the parameter is not an array or MetadataInterface.
     * @return self
     */
    public function setMetadata($metadata)
    {
        if (is_array($metadata)) {
            $meta = $this->createMetadata();
            $meta->merge($metadata);
            $this->metadata = $meta;
        } elseif ($metadata instanceof MetadataInterface) {
            $this->metadata = $metadata;
        } else {
            throw new InvalidArgumentException(
                'Metadata argument is invalid (must be array or Metadata object).'
            );
        }

        return $this;
    }

    /**
     * Retrieve metadata, or load it if it was not explicitly set.
     *
     * @return MetadataInterface
     */
    public function metadata()
    {
        if ($this->metadata === null) {
            return $this->loadMetadata();
        }
        return $this->metadata;
    }

    /**
     * Load a metadata file and store it as a static var.
     *
     * Use a `MetadataLoader` object and the object's metadataIdent
     * to load the metadata content (typically from the filesystem, as json).
     *
     * @param  string $metadataIdent Optional ident. If none is provided then it will use the auto-genereated one.
     * @return MetadataInterface
     */
    public function loadMetadata($metadataIdent = null)
    {
        if ($metadataIdent === null) {
            $metadataIdent = $this->metadataIdent();
        }

        $metadata = $this->metadataLoader()->load($metadataIdent, $this->metadataClass());
        $this->setMetadata($metadata);

        return $metadata;
    }

    /**
     * @param string $metadataIdent The metadata ident.
     * @return self
     */
    public function setMetadataIdent($metadataIdent)
    {
        $this->metadataIdent = $metadataIdent;
        return $this;
    }

    /**
     * Get the metadata ident, or generate it from class name if it was not set explicitly.
     *
     * @return string
     */
    public function metadataIdent()
    {
        if ($this->metadataIdent === null) {
            $this->metadataIdent = $this->generateMetadataIdent();
        }
        return $this->metadataIdent;
    }

    /**
     * Create a new metadata object.
     *
     * @return MetadataInterface
     */
    abstract protected function createMetadata();

    /**
     * Retrieve the class name of the metadata object.
     *
     * @return string
     */
    abstract protected function metadataClass();

    /**
     * @param MetadataLoader $loader The loader instance, used to load metadata.
     * @return self
     */
    protected function setMetadataLoader(MetadataLoader $loader)
    {
        $this->metadataLoader = $loader;
        return $this;
    }

    /**
     * Safe MetadataLoader getter. Create the loader if it does not exist.
     *
     * @throws RuntimeException If the metadata loader was not set.
     * @return MetadataLoader
     */
    protected function metadataLoader()
    {
        if (!$this->metadataLoader) {
            throw new RuntimeException(
                sprintf('Metadata loader was not set for "%s"', get_class($this))
            );
        }
        return $this->metadataLoader;
    }

    /**
     * Generate a metadata identifier from this object's class name (FQN).
     *
     * Converts the short class name and converts it from camelCase to kebab-case.
     *
     * @return string
     */
    protected function generateMetadataIdent()
    {
        $ident = preg_replace('/([a-z])([A-Z])/', '$1-$2', static::class);
        $ident = strtolower(str_replace('\\', '/', $ident));
        return $ident;
    }
}
