<?php

namespace Charcoal\Metadata;

// Dependencies from `PHP`
use \Exception as Exception;
use \InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Property\PropertyFactory;

// Local namespace dependencies
use \Charcoal\Metadata\MetadataLoader;
use \Charcoal\Metadata\MetadataInterface;

/**
* Default implementation, as trait, of the `DescribableInterface`.
*
* This trait adds 3 abstract methods:
* - `set_data()`
* - `create_metadata()`
* - `property_value()`
*/
trait DescribableTrait
{
    /**
    * @var MetadataInterface $metadata
    */
    protected $metadata;

    /**
    * @var string $metadata_ident
    */
    protected $metadata_ident;

    /**
    * @var array $properties
    */
    protected $properties;

    /**
    * Describable object needs to have a `set_data()` method
    *
    * @param array $data
    * @return DescribableTrait Chainable
    */
    abstract public function set_data(array $data);

    /**
    * @param array $data
    * @throws InvalidArgumentException
    * @return DescribableTrait
    */
    public function set_describable_data(array $data)
    {
        if (isset($data['metadata']) && $data['metadata'] !== null) {
            $this->set_metadata($data['metadata']);
        }
        if (isset($data['metdata_ident']) && $data['metadata_ident'] !== null) {
            $this->set_metadata_ident($data['metadata_ident']);
        }
        return $this;
    }

    /**
    * @param array|MetadataInterface $metadata
    * @throws InvalidArgumentException if the parameter is not an array or MetadataInterface
    * @return DescribableInterface Chainable
    */
    public function set_metadata($metadata)
    {
        if (is_array($metadata)) {
            $meta = $this->create_metadata();
            $meta->set_data($metadata);
            $this->metadata = $meta;
        } elseif ($metadata instanceof MetadataInterface) {
            $this->metadata = $metadata;
        } else {
            throw new InvalidArgumentException('Metadata argument is invalid (must be array or Medatadata object).');
        }

        // If the metadata contains "data", then automatically set the initial data to the value
        if (isset($this->metadata['data']) && is_array($this->metadata['data'])) {
            $this->set_data($this->metadata['data']);
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
            return $this->load_metadata();
        }
        return $this->metadata;
    }

    /**
    * Load a metadata file and store it as a static var.
    *
    * Use a `MetadataLoader` object and the object's metadata_ident
    * to load the metadata content (typically from the filesystem, as json).
    *
    * @param string $metadata_ident Optional ident
    * @return MetadataInterface
    */
    public function load_metadata($metadata_ident = null)
    {
        if ($metadata_ident === null) {
            $metadata_ident = $this->metadata_ident();
        }
        $metadata_loader = new MetadataLoader();
        $metadata = $metadata_loader->load($metadata_ident);
        $this->set_metadata($metadata);

        return $metadata;
    }

    /**
    * @return MetadataInterface
    */
    abstract protected function create_metadata();

    /**
    * @param string $metadata_ident
    * @return DescribableInterface Chainable
    */
    public function set_metadata_ident($metadata_ident)
    {
        $this->metadata_ident = $metadata_ident;
        return $this;
    }

    /**
    * Get the metadata ident, or generate it from class name.
    *
    * @return string
    */
    public function metadata_ident()
    {
        if ($this->metadata_ident === null) {
            $this->metadata_ident = $this->generate_metadata_ident();
        }
        return $this->metadata_ident;
    }

    /**
    * Generate a metadata ident from class name.
    *
    * Change `\` and `.` to `/` and force lowercase
    *
    * @return string
    */
    protected function generate_metadata_ident()
    {
        $class_name = get_class($this);
        $metadata_ident = str_replace(['\\', '.'], '/', strtolower($class_name));
        return $metadata_ident;
    }

    /**
    * Return an array of `PropertyInterface`
    *
    * @return void Yield, not return
    */
    public function properties()
    {
        $this->metadata(); // Hack!
        $props = $this->metadata()->properties();

        if (empty($props)) {
            yield null;
        }

        $properties = [];
        foreach ($props as $property_ident => $opts) {
            // Get the property object of this definition
            yield $property_ident => $this->property($property_ident);
        }
    }

    /**
    * Get an object's property
    *
    * @param string $property_ident The property ident to return
    * @throws InvalidArgumentException if the property_ident is not a string
    * @throws Exception if the requested property is invalid
    * @return PropertyInterface The \Charcoal\Model\Property if found, null otherwise
    */
    public function property($property_ident)
    {
        if (!is_string($property_ident)) {
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

        if (!isset($props[$property_ident])) {
            throw new Exception(
                sprintf('Invalid property: %s (not defined in metadata).', $property_ident)
            );
        }

        $property_metadata = $props[$property_ident];
        if (!isset($property_metadata['type'])) {
            throw new Exception(
                sprintf('Invalid property: %s (type is undefined).', $property_ident)
            );
        }

        $property = PropertyFactory::instance()->create($property_metadata['type']);
        $property->set_ident($property_ident);
        $property->set_data($property_metadata);

        $property_value = $this->property_value($property_ident);
        if ($property_value !== null) {
            $property->set_val($property_value);
        }

        return $property;
    }

    /**
    * Shortcut (alias) for:
    * - `property()` if the first parameter is set,
    * - `properties()` if the property_ident is not set (null)
    *
    * @param string $property_ident The property ident to return
    * @return array|PropertyInterface
    */
    public function p($property_ident = null)
    {
        if ($property_ident === null) {
            return $this->properties();
        }
        // Alias for property()
        return $this->property($property_ident);
    }

    /**
    * @throws InvalidArgumentException
    * @return boolean
    */
    public function has_property($property_ident)
    {
        if (!is_string($property_ident)) {
            throw new InvalidArgumentException(
                'Property Ident must be a string.'
            );
        }

        $metadata = $this->metadata();
        $props = $metadata->properties();

        return isset($props[$property_ident]);
    }

    /**
    * @param string $property_ident
    * @return mixed
    */
    abstract protected function property_value($property_ident);
}
