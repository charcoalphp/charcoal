<?php

namespace Charcoal\Model;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// Module (`charcoal-property`) dependencies
use \Charcoal\Property\PropertyFactory;

// Local namespace dependencies
use \Charcoal\Model\MetadataLoader;
use \Charcoal\Model\MetadataInterface;

/**
* Default implementation, as trait, of the `DescribableInterface`.
*
* This trait adds 3 abstract methods:
* - `setData()`
* - `createMetadata()`
* - `property_value()`
*/
trait DescribableTrait
{
    /**
     * @var PropertyFactory $propertyFactory
     */
    protected $propertyFactory;

    /**
     * @var MetadataLoader $metadataLoader
     */
    protected $metadataLoader;

    /**
    * @var MetadataInterface $metadata
    */
    protected $metadata;

    /**
    * @var string $metadataIdent
    */
    protected $metadataIdent;

    /**
    * @var array $properties
    */
    protected $properties;

    /**
    * Describable object needs to have a `setData()` method
    *
    * @param array $data
    * @return DescribableInterface Chainable
    */
    abstract public function setData(array $data);

    /**
     * @param PropertyFactory $factory The property factory, used to create metadata properties.
     * @return DescribableInterface Chainable
     */
    public function setPropertyFactory(PropertyFactory $factory)
    {
        $this->propertyFactory = $propertyFactory;
        return $this;
    }

    /**
     * Safe PropertyFactory getter. Create the factory if it does not exist.
     *
     * @return PropertyFactory
     */
    protected function propertyFactory()
    {
        if (isset($this->propertyFactory)) {
            $this->propertyFactory = new PropertyFactory();
        }
        return $this->propertyFactory;
    }

    /**
     * @param MedataLoader $loader The loader instance, used to load metadata.
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
     * @return MetadataLoader
     */
    protected function metatadataLoader()
    {
        if (!isset($this->metadataLoader)) {
            $this->metadataLoader = new MetadataLoader();
        }
        return $this->metadataLoader;
    }


    /**
    * @param array|MetadataInterface $metadata
    * @throws InvalidArgumentException if the parameter is not an array or MetadataInterface
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
                'Metadata argument is invalid (must be array or Medatadata object).'
            );
        }

        // If the metadata contains "data", then automatically set the initial data to the value
        if (isset($this->metadata['data']) && is_array($this->metadata['data'])) {
            $this->setData($this->metadata['data']);
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
    * @param string $metadataIdent Optional ident
    * @return MetadataInterface
    */
    public function loadMetadata($metadataIdent = null)
    {
        if ($metadataIdent === null) {
            $metadataIdent = $this->metadataIdent();
        }

        $metadataLoader = $this->metadataLoader();
        $metadata = $metadataLoader->load($metadataIdent);
        $this->setMetadata($metadata);

        return $metadata;
    }

    /**
    * @return MetadataInterface
    */
    abstract protected function createMetadata();

    /**
    * @param string $metadataIdent
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

    /**
    * Return an array of `PropertyInterface`
    *
    * @return void Yield, not return
    */
    public function properties(array $filters = null)
    {
        $this->metadata(); // Hack!
        $props = array_keys($this->metadata()->properties());

        if (empty($props)) {
            yield null;
        }

        foreach ($props as $propertyIdent) {
            $property = $this->property($propertyIdent);
            $filtered = (int)$property->isFiltered($filters);
            // Get the property object of this definition
            yield $propertyIdent => $this->property($propertyIdent);
        }
    }

    /**
    * Get an object's property
    *
    * @param string $propertyIdent The property ident to return
    * @throws InvalidArgumentException if the propertyIdent is not a string
    * @throws Exception if the requested property is invalid
    * @return PropertyInterface The \Charcoal\Model\Property if found, null otherwise
    */
    public function property($propertyIdent)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property Ident must be a string.'
            );
        }

        $metadata = $this->metadata();
        $props = $metadata->properties();

        if (empty($props)) {
            throw new Exception(sprintf(
                'Invalid model metadata (%s) - No properties defined.',
                get_class($this)
            ));
        }

        if (!isset($props[$propertyIdent])) {
            throw new Exception(
                sprintf('Invalid property: %s (not defined in metadata).', $propertyIdent)
            );
        }

        $propertyMetadata = $props[$propertyIdent];
        if (!isset($propertyMetadata['type'])) {
            throw new Exception(
                sprintf('Invalid %s property: %s (type is undefined).', get_class($this), $propertyIdent)
            );
        }

        $factory  = $this->propertyFactory();
        $property = $factory->create($propertyMetadata['type'], [
            'logger' => $this->logger
        ]);
        $property->setIdent($propertyIdent);
        $property->setData($propertyMetadata);

        $property_value = $this->propertyValue($propertyIdent);
        if ($property_value !== null) {
            $property->setVal($property_value);
        }

        return $property;
    }

    /**
    * Shortcut (alias) for:
    * - `property()` if the first parameter is set,
    * - `properties()` if the propertyIdent is not set (null)
    *
    * @param string $propertyIdent The property ident to return
    * @return array|PropertyInterface
    */
    public function p($propertyIdent = null)
    {
        if ($propertyIdent === null) {
            return $this->properties();
        }
        // Alias for property()
        return $this->property($propertyIdent);
    }

    /**
    * @throws InvalidArgumentException
    * @return boolean
    */
    public function hasProperty($propertyIdent)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property Ident must be a string.'
            );
        }

        $metadata = $this->metadata();
        $props = $metadata->properties();

        return isset($props[$propertyIdent]);
    }

    /**
    * @param string $propertyIdent
    * @return mixed
    */
    abstract protected function propertyValue($propertyIdent);

    /**
    * @param array $filters The filters to apply
    * @return boolean False if the object doesn't match any filter, true otherwise.
    */
    public function isFiltered(array $filters = null)
    {
        if ($filters === null) {
            return false;
        }

        foreach ($filters as $filterIdent => $filterData) {
            unset($filterIdent);
            unset($filterData);
        }

        return false;
    }
}
