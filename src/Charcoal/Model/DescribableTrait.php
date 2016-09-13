<?php

namespace Charcoal\Model;

use \Exception;
use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Model\MetadataLoader;
use \Charcoal\Model\MetadataInterface;

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
     * Describable object needs to have a `setData()` method
     *
     * @param array|\Traversable $data The object's data.
     * @return DescribableInterface Chainable
     */
    abstract public function setData($data);

    /**
     * @param MetadataLoader $loader The loader instance, used to load metadata.
     * @return DescribableInterface Chainable
     */
    public function setMetadataLoader(MetadataLoader $loader)
    {
        $this->metadataLoader = $loader;
        return $this;
    }

    /**
     * Safe MetdataLoader getter. Create the loader if it does not exist.
     *
     * @throws Exception If the metadata loader was not set.
     * @return MetadataLoader
     */
    protected function metadataLoader()
    {
        if (!$this->metadataLoader) {
            throw new Exception(
                sprintf('Metadata loader was not set for "%s"', get_class($this))
            );
        }
        return $this->metadataLoader;
    }

    /**
     * @param array|MetadataInterface $metadata The object's metadata.
     * @throws InvalidArgumentException If the parameter is not an array or MetadataInterface.
     * @return DescribableInterface Chainable
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

        // Chainable
        return $this;
    }

    /**
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
     * @param string $metadataIdent Optional ident.
     * @return MetadataInterface
     */
    public function loadMetadata($metadataIdent = null)
    {
        if ($metadataIdent === null) {
            $metadataIdent = $this->metadataIdent();
        }

        $metadataLoader = $this->metadataLoader();
        $metadata = $metadataLoader->load($metadataIdent, $this->createMetadata());
        $this->setMetadata($metadata);

        return $metadata;
    }

    /**
     * @return MetadataInterface
     */
    abstract protected function createMetadata();

    /**
     * @param string $metadataIdent The metadata ident.
     * @return DescribableInterface Chainable
     */
    public function setMetadataIdent($metadataIdent)
    {
        $this->metadataIdent = $metadataIdent;
        return $this;
    }

    /**
     * Get the metadata ident, or generate it from class name.
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
     * Generate a metadata ident from class name.
     *
     * Change `\` and `.` to `/` and force lowercase
     *
     * @return string
     */
    protected function generateMetadataIdent()
    {
        $classname = get_class($this);
        $ident = preg_replace('/([a-z])([A-Z])/', '$1-$2', $classname);
        $metadataIdent = strtolower(str_replace('\\', '/', $ident));
        return $metadataIdent;
    }
}
