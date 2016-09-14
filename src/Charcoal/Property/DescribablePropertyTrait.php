<?php

namespace Charcoal\Property;

use \Exception;
use \InvalidArgumentException;
use \RuntimeException;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\FactoryInterface;

// Local namespace dependency
use \Charcoal\Property\PropertyFactory;

/**
 * Default implementation, as trait, of the {@see DescribablePropertyInterface}.
 *
 * Complements {@see DescribableInterface}.
 */
trait DescribablePropertyTrait
{
    /**
     * @var FactoryInterface $propertyFactory
     */
    protected $propertyFactory;

    /**
     * @var array $properties
     */
    protected $properties = [];

    /**
     * @param FactoryInterface $factory The property factory, used to create metadata properties.
     * @return DescribableInterface Chainable
     */
    protected function setPropertyFactory(FactoryInterface $factory)
    {
        $this->propertyFactory = $factory;

        return $this;
    }

    /**
     * Safe PropertyFactory getter. Create the factory if it does not exist.
     *
     * @throws RuntimeException If no property factory has been previously set.
     * @return FactoryInterface
     */
    public function propertyFactory()
    {
        if ($this->propertyFactory === null) {
            throw new RuntimeException(
                'Model does not have a property factory.'
            );
        }

        return $this->propertyFactory;
    }

    /**
     * Return an array of `PropertyInterface`
     *
     * @return PropertyInterface[] Generator.
     */
    public function properties()
    {
        // Hack: ensure metadata is loaded.
        $this->metadata();

        $props = array_keys($this->metadata()->properties());

        if (empty($props)) {
            yield null;
        }

        foreach ($props as $propertyIdent) {
            yield $propertyIdent => $this->property($propertyIdent);
        }
    }

    /**
     * Get an object's property
     *
     * @param string $propertyIdent The property ident to return.
     * @throws InvalidArgumentException If the propertyIdent is not a string.
     * @throws Exception If the requested property is invalid.
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
        $propertyObject = $metadata->propertyObject($propertyIdent);
        if ($propertyObject !== null) {
            return $propertyObject;
        }


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
        $property = $factory->create($propertyMetadata['type']);
        $property->metadata();
        $property->setIdent($propertyIdent);
        $property->setData($propertyMetadata);

        // $propertyValue = $this->propertyValue($propertyIdent);
        // if ($propertyValue !== null) {
        //     $property->setVal($propertyValue);
        // }

        $metadata->setPropertyObject($propertyIdent, $property);
        return $property;
    }

    /**
     * Alias of {@see DescribablePropertyInterface::property()} and
     * {@see DescribablePropertyInterface::properties()}.
     *
     * Shortcut for:
     *
     * - `property()` if the first parameter is set,
     * - `properties()` if the $propertyIdent is not set (NULL)
     *
     * @param string $propertyIdent The property ident to return.
     * @return array|PropertyInterface
     */
    public function p($propertyIdent = null)
    {
        if ($propertyIdent === null) {
            return $this->properties();
        }

        return $this->property($propertyIdent);
    }

    /**
     * @param  string $propertyIdent The ident of the property to check.
     * @throws InvalidArgumentException If the ident argument is not a string.
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
     * @param string $propertyIdent The property ident to retrieve the value for.
     * @return mixed
     */
    abstract protected function propertyValue($propertyIdent);
}
