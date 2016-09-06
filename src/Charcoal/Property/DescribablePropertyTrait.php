<?php

namespace Charcoal\Property;

use \Exception;
use \InvalidArgumentException;

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
    protected $properties;

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
     * @return FactoryInterface
     */
    public function propertyFactory()
    {
        if ($this->propertyFactory === null) {
            $this->logger->warning(
                sprintf('Creating a property factory for describable %s', get_class($this))
            );
            $this->propertyFactory = new PropertyFactory();
        }

        return $this->propertyFactory;
    }

    /**
     * Return an array of `PropertyInterface`
     *
     * @param array|null $filters Unused.
     * @return PropertyInterface[] Generator.
     */
    public function properties(array $filters = null)
    {
        $this->metadata();
        // Hack!
        $props = array_keys($this->metadata()->properties());

        if (empty($props)) {
            yield null;
        }

        foreach ($props as $propertyIdent) {
            $property = $this->property($propertyIdent);
            $filtered = (int)$property->isFiltered($filters);
            // Get the property object of this definition
            yield $propertyIdent => $property;
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

        $property_value = $this->propertyValue($propertyIdent);
        if ($property_value !== null) {
            $property->setVal($property_value);
        }

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

    /**
     * @todo Implement property filters.
     * @param array $filters The filters to apply.
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
