<?php

namespace Charcoal\Model;

use PDO;
use PDOException;
use DateTimeInterface;
use UnexpectedValueException;
// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
// From Pimple
use Pimple\Container;
// From 'charcoal-config'
use Charcoal\Config\AbstractEntity;
// From 'charcoal-view'
use Charcoal\View\ViewableInterface;
use Charcoal\View\ViewableTrait;
// From 'charcoal-property'
use Charcoal\Property\DescribablePropertyInterface;
use Charcoal\Property\DescribablePropertyTrait;
use Charcoal\Property\PropertyInterface;
// From 'charcoal-core'
use Charcoal\Model\DescribableInterface;
use Charcoal\Model\DescribableTrait;
use Charcoal\Model\ModelInterface;
use Charcoal\Model\ModelMetadata;
use Charcoal\Model\ModelValidator;
use Charcoal\Source\StorableTrait;
use Charcoal\Validator\ValidatableInterface;
use Charcoal\Validator\ValidatableTrait;

/**
 * An abstract class that implements most of `ModelInterface`.
 *
 * In addition to `ModelInterface`, the abstract model implements the following interfaces:
 *
 * - `DescribableInterface`
 * - `StorableInterface
 * - `ValidatableInterface`
 * - `ViewableInterface`.
 *
 * Those interfaces are implemented (in parts, at least) with
 * `DescribableTrait`, `StorableTrait`, `ValidatableTrait`, and `ViewableTrait`.
 *
 * The `JsonSerializable` interface is fully provided by the `DescribableTrait`.
 */
abstract class AbstractModel extends AbstractEntity implements
    ModelInterface,
    DescribablePropertyInterface,
    LoggerAwareInterface,
    ValidatableInterface,
    ViewableInterface
{
    use LoggerAwareTrait;
    use DescribableTrait;
    use DescribablePropertyTrait;
    use StorableTrait;
    use ValidatableTrait;
    use ViewableTrait;

    public const DEFAULT_SOURCE_TYPE = 'database';

    /**
     * @param array $data Dependencies.
     */
    public function __construct(array $data = null)
    {
        // LoggerAwareInterface dependencies
        $this->setLogger($data['logger']);

        // Optional DescribableInterface dependencies
        if (isset($data['property_factory'])) {
            $this->setPropertyFactory($data['property_factory']);
        }
        if (isset($data['metadata'])) {
            $this->setMetadata($data['metadata']);
        }
        if (isset($data['metadata_loader'])) {
            $this->setMetadataLoader($data['metadata_loader']);
        }

        // Optional StorableInterface dependencies
        if (isset($data['source'])) {
             $this->setSource($data['source']);
        }
        if (isset($data['source_factory'])) {
            $this->setSourceFactory($data['source_factory']);
        }

        // Optional ViewableInterface dependencies
        if (isset($data['view'])) {
            $this->setView($data['view']);
        }

        // Optional dependencies injection via Pimple Container
        if (isset($data['container'])) {
            $this->setDependencies($data['container']);
        }
    }

    /**
     * Retrieve the model data as a structure (serialize to array).
     *
     * @param  array $properties Optional. List of property identifiers
     *     for retrieving a subset of data.
     * @return array
     */
    public function data(array $properties = null)
    {
        $data = [];
        $properties = $this->properties($properties);
        foreach ($properties as $propertyIdent => $property) {
            // Ensure objects are properly encoded.
            $val = $this->propertyValue($propertyIdent);
            $val = $this->serializedValue($val);
            $data[$propertyIdent] = $val;
        }

        return $data;
    }

    /**
     * Sets the object data, from an associative array map (or any other Traversable).
     *
     * @param  array $data The entity data. Will call setters.
     * @return self
     * @see AbstractEntity::setData()
     */
    public function setData(array $data)
    {
        $data = $this->setIdFromData($data);

        parent::setData($data);
        return $this;
    }

    /**
     * Sets the object data, from an associative array map (or any other Traversable).
     *
     * @param  array $data The model property data.
     * @return array Returns the remaining dataset.
     */
    public function setPropertyData(array $data)
    {
        $data = $this->setIdFromData($data);

        foreach ($data as $key => $value) {
            if ($this->hasProperty($key)) {
                $this[$key] = $value;
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Merge data on the model.
     *
     * Overrides `\Charcoal\Config\AbstractEntity::setData()`
     * to take properties into consideration.
     *
     * Also add a special case, to merge values for l10n properties.
     *
     * @param  array $data The data to merge.
     * @return self
     */
    public function mergeData(array $data)
    {
        $data = $this->setIdFromData($data);

        foreach ($data as $propIdent => $val) {
            if (!$this->hasProperty($propIdent)) {
                $this->logger->warning(sprintf(
                    'Cannot set property "%s" on object; not defined in metadata.',
                    $propIdent
                ));
                continue;
            }

            $property = $this->p($propIdent);
            if ($property['l10n'] && is_array($val)) {
                $currentValue = json_decode(json_encode($this[$propIdent]), true);
                if (is_array($currentValue)) {
                    $this[$propIdent] = array_merge($currentValue, $val);
                } else {
                    $this[$propIdent] = $val;
                }
            } else {
                $this[$propIdent] = $val;
            }
        }

        return $this;
    }

    /**
     * Retrieve the default values, from the model's metadata.
     *
     * @return array
     */
    public function defaultData()
    {
        $metadata = $this->metadata();
        return $metadata->defaultData();
    }

    /**
     * Set the model data (from a flattened structure).
     *
     * @param  array $flatData The model dataset.
     * @return self
     */
    public function setFlatData(array $flatData)
    {
        $flatData = $this->setPropertyDataFromFlatData($flatData);

        // Set remaining (non-property) data.
        if (!empty($flatData)) {
            $this->setData($flatData);
        }

        return $this;
    }

    /**
     * Set the model property data (from a flattened structure).
     *
     * This method takes a one-dimensional dataset and, depending on the property's
     * {@see \Charcoal\Property\PropertyField::fieldNames() field structure},
     * returns a {@see \Charcoal\Property\PropertyField::parseFromFlatData() complex datum}.
     *
     * @param  array $flatData The model property data.
     * @return array Returns the remaining dataset.
     */
    public function setPropertyDataFromFlatData(array $flatData)
    {
        $flatData = $this->setIdFromData($flatData);

        $propData   = [];
        $properties = $this->properties();
        foreach ($properties as $propertyIdent => $property) {
            $fieldValues = [];
            $fieldNames  = $property->fieldNames();
            foreach ($fieldNames as $fieldName) {
                if (array_key_exists($fieldName, $flatData)) {
                    $fieldValues[$fieldName] = $flatData[$fieldName];
                    unset($flatData[$fieldName]);
                }
            }

            if ($fieldValues) {
                $this[$propertyIdent] = $property->parseFromFlatData($fieldValues);
            }
        }

        return $flatData;
    }

    /**
     * Retrieve the model data as a flattened structure.
     *
     * This method returns a 1-dimensional array of the object's values.
     *
     * @param  array $properties Optional. List of property identifiers
     *     for retrieving a subset of data.
     * @return array
     */
    public function flatData(array $properties = null)
    {
        $flatData   = [];
        $properties = $this->properties($properties);
        foreach ($properties as $propertyIdent => $property) {
            $value = $this->propertyValue($propertyIdent);
            foreach ($property->fields($value) as $field) {
                $flatData[$field->ident()] = $field->val();
            }
        }

        return $flatData;
    }

    /**
     * Retrieve the value for the given property.
     * Force camelcase on the parameter.
     *
     * @param  string $propertyIdent The property identifier to fetch.
     * @return mixed
     */
    public function propertyValue($propertyIdent)
    {
        $propertyIdent = $this->camelize($propertyIdent);
        return $this[$propertyIdent];
    }

    /**
     * @param array $properties Optional array of properties to save. If null, use all object's properties.
     * @return boolean
     */
    public function saveProperties(array $properties = null)
    {
        if ($properties === null) {
            $properties = array_keys($this->metadata()->properties());
        }

        foreach ($properties as $propertyIdent) {
            $p = $this->p($propertyIdent);

            $v = $p->getStorable()
                ? $p->save($this->propertyValue($propertyIdent))
                : null;

            if ($v === null) {
                continue;
            }

            $this[$propertyIdent] = $v;
        }

        return true;
    }

    /**
     * Load an object from the database from its l10n key $key.
     * Also retrieve and return the actual language that matched.
     *
     * @param  string $key   Key pointing a column's l10n base ident.
     * @param  mixed  $value Value to search in all languages.
     * @param  array  $langs List of languages (code, ex: "en") to check into.
     * @throws InvalidArgumentException If a language is invalid.
     * @throws PDOException If the PDO query fails.
     * @return string The matching language.
     */
    public function loadFromL10n($key, $value, array $langs)
    {
        $binds = [
            'ident' => $value,
        ];
        $switch = [];
        $where  = [];
        foreach ($langs as $lang) {
            if (!is_string($lang)) {
                throw new InvalidArgumentException('Language; must be a string');
            }

            $fieldName = $key . '_' . $lang;
            $langParam = 'lang_' . $lang;
            $binds[$langParam] = $lang;
            $langParam = ':' . $langParam;

            $switch[] = 'WHEN `' . $fieldName . '` = :ident THEN ' . $langParam;
            $where[]  = '`' . $fieldName . '` = :ident';
        }

        $source = $this->source();

        $sql  = 'SELECT *, (CASE ' . implode("\n", $switch) . ' END) AS _lang ';
        $sql .= 'FROM `' . $source->table() . '` ';
        $sql .= 'WHERE (' . implode(' OR ', $where) . ') LIMIT 1';

        $sth = $source->dbQuery($sql, $binds);
        if ($sth === false) {
            throw new PDOException(sprintf(
                'Could not load model [%s] for localized column "%s" [%s]',
                get_class($this),
                $fieldName,
                (is_object($value) ? get_class($value) : (is_string($value) ? $value : gettype($value)))
            ));
        }

        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if (!$data || !isset($data['_lang'])) {
            throw new PDOException(sprintf(
                'Unable to retrieve model [%s] data for localized column "%s" [%s]',
                get_class($this),
                $fieldName,
                (is_object($value) ? get_class($value) : (is_string($value) ? $value : gettype($value)))
            ));
        }

        $lang = $data['_lang'];
        unset($data['_lang']);

        if ($data) {
            $this->setFlatData($data);
        }

        return $lang;
    }

    /**
     * Set the object's ID from an associative array map (or any other Traversable).
     *
     * Useful for setting the object ID before the rest of the object's data.
     *
     * @param  array $data The object data.
     * @return array Returns the remaining dataset.
     */
    protected function setIdFromData(array $data)
    {
        $key = $this->key();
        if (isset($data[$key])) {
            $this->setId($data[$key]);
            unset($data[$key]);
        }

        return $data;
    }

    /**
     * Serialize the given value.
     *
     * @param  mixed $val The value to serialize.
     * @return mixed
     */
    protected function serializedValue($val)
    {
        if (is_scalar($val)) {
            return $val;
        } elseif ($val instanceof DateTimeInterface) {
            return $val->format('Y-m-d H:i:s');
        } else {
            return json_decode(json_encode($val), true);
        }
    }

    /**
     * Save event called (in storable trait) before saving the model.
     *
     * @see StorableTrait::preSave()
     * @return boolean
     */
    protected function preSave()
    {
        return $this->saveProperties();
    }

    /**
     * StorableTrait > preUpdate(). Update hook called before updating the model.
     *
     * @param string[] $properties Optional. The properties to update.
     * @see StorableTrait::preUpdate()
     * @return boolean
     */
    protected function preUpdate(array $properties = null)
    {
        return $this->saveProperties($properties);
    }

    /**
     * Create a new metadata object.
     *
     * @see DescribablePropertyTrait::createMetadata()
     * @return ModelMetadata
     */
    protected function createMetadata()
    {
        $class = $this->metadataClass();
        return new $class();
    }

    /**
     * Retrieve the class name of the metadata object.
     *
     * @see DescribableTrait::metadataClass()
     * @return string
     */
    protected function metadataClass()
    {
        return ModelMetadata::class;
    }

    /**
     * @throws UnexpectedValueException If the metadata source can not be found.
     * @see StorableTrait::createSource()
     * @return \Charcoal\Source\SourceInterface
     */
    protected function createSource()
    {
        $metadata      = $this->metadata();
        $defaultSource = $metadata->defaultSource();
        $sourceConfig  = $metadata->source($defaultSource);

        if (!$sourceConfig) {
            throw new UnexpectedValueException(sprintf(
                'Can not create source for model [%s]: Invalid metadata (can not load source\'s configuration)',
                get_class($this)
            ));
        }

        $type   = isset($sourceConfig['type']) ? $sourceConfig['type'] : self::DEFAULT_SOURCE_TYPE;
        $source = $this->sourceFactory()->create($type);
        $source->setModel($this);

        $source->setData($sourceConfig);

        return $source;
    }

    /**
     * ValidatableInterface > create_validator().
     *
     * @return \Charcoal\Validator\ValidatorInterface
     */
    protected function createValidator()
    {
        $validator = new ModelValidator($this);
        return $validator;
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A Pimple DI service container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        // This method is a stub.
        // Reimplement in children method to inject dependencies in your class from a Pimple container.
    }

    /**
     * Generate a model type identifier from this object's class name.
     *
     * Based on {@see DescribableTrait::generateMetadataIdent()}.
     *
     * @return string
     */
    public static function objType()
    {
        $class = get_called_class();
        $ident = preg_replace('/([a-z])([A-Z])/', '$1-$2', $class);
        $ident = strtolower(str_replace('\\', '/', $ident));
        return $ident;
    }
}
