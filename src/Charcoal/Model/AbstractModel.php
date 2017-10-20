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
use Charcoal\View\GenericView;
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
use Charcoal\Source\SourceFactory;
use Charcoal\Source\StorableInterface;
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
    DescribableInterface,
    DescribablePropertyInterface,
    LoggerAwareInterface,
    StorableInterface,
    ValidatableInterface,
    ViewableInterface
{
    use LoggerAwareTrait;
    use DescribableTrait;
    use DescribablePropertyTrait;
    use StorableTrait;
    use ValidatableTrait;
    use ViewableTrait;

    const DEFAULT_SOURCE_TYPE = 'database';

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

        if (isset($data['container'])) {
            $this->setDependencies($data['container']);
        }
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A service container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        // This method is a stub. Reimplement in children method
    }

    /**
     * Set the object's ID from an associative array map (or any other Traversable).
     *
     * Useful for setting the object ID before the rest of the object's data.
     *
     * @param  array $data The object data.
     * @return array The object data without the pre-set ID.
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
     * Sets the object data, from an associative array map (or any other Traversable).
     *
     * @param  array $data The entity data. Will call setters.
     * @return self
     */
    public function setData(array $data)
    {
        $data = $this->setIdFromData($data);

        return parent::setData($data);
    }

    /**
     * Retrieve the model data as a structure.
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
     * Merge data on the model.
     *
     * Overrides `\Charcoal\Config\AbstractEntity::setData()`
     * to take properties into consideration.
     *
     * Also add a special case, to merge values for l10n properties.
     *
     * @param  array $data The data to merge.
     * @return EntityInterface Chainable
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
            if ($property->l10n() && is_array($val)) {
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
     * Retrieve the default values.
     *
     * @return array From the model's metadata.
     */
    public function defaultData()
    {
        $metadata = $this->metadata();
        return $metadata->defaultData();
    }

    /**
     * Set the model data (from a flattened structure).
     *
     * This method takes a 1-dimensional array and fills the object with its values.
     *
     * @param  array $flatData The model data.
     * @return self
     */
    public function setFlatData(array $flatData)
    {
        $flatData = $this->setIdFromData($flatData);

        $data = [];
        $properties = $this->properties();
        foreach ($properties as $propertyIdent => $property) {
            $fields = $property->fields(null);
            foreach ($fields as $k => $f) {
                if (is_string($k)) {
                    $fid = $f->ident();
                    $key = str_replace($propertyIdent.'_', '', $fid);
                    if (isset($flatData[$fid])) {
                        $data[$propertyIdent][$key] = $flatData[$fid];
                        unset($flatData[$fid]);
                    }
                } else {
                    $fid = $f->ident();
                    if (isset($flatData[$fid])) {
                        $data[$propertyIdent] = $flatData[$fid];
                        unset($flatData[$fid]);
                    }
                }
            }
        }

        $this->setData($data);

        // Set remaining (non-property) data.
        if (!empty($flatData)) {
            $this->setData($flatData);
        }

        return $this;
    }

    /**
     * Retrieve the model data as a flattened structure.
     *
     * This method returns a 1-dimensional array of the object's values.
     *
     * @todo   Implementation required.
     * @return array
     */
    public function flatData()
    {
        return [];
    }

    /**
     * Retrieve the value for the given property.
     *
     * @param  string $propertyIdent The property identifier to fetch.
     * @return mixed
     */
    public function propertyValue($propertyIdent)
    {
        $getter = $this->getter($propertyIdent);
        $method = [ $this, $getter ];

        if (is_callable($method)) {
            return call_user_func($method);
        } elseif (isset($this->{$propertyIdent})) {
            return $this->{$propertyIdent};
        }

        return null;
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
            $v = $p->save($this->propertyValue($propertyIdent));

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
     * @throws PDOException If the PDO query fails.
     * @return string The matching language.
     */
    public function loadFromL10n($key, $value, array $langs)
    {
        $switch = [];
        $where = [];
        foreach ($langs as $lang) {
            $switch[] = 'when `'.$key.'_'.$lang.'` = :ident then \''.$lang.'\'';
            $where[] = '`'.$key.'_'.$lang.'` = :ident';
        }

        $q = '
            SELECT
                *,
                (case
                    '.implode("\n", $switch).'
                end) as _lang
            FROM
               `'.$this->source()->table().'`
            WHERE
                ('.implode(' OR ', $where).')
            LIMIT
               1';

        $binds = [
            'ident' => $value
        ];

        $sth = $this->source()->dbQuery($q, $binds);
        if ($sth === false) {
            throw new PDOException('Could not load item.');
        }

        $data = $sth->fetch(PDO::FETCH_ASSOC);
        $lang = $data['_lang'];
        unset($data['_lang']);

        if ($data) {
            $this->setFlatData($data);
        }

        return $lang;
    }

    /**
     * Save the object's current state to storage.
     *
     * Overrides default StorableTrait save() method to also save properties.
     *
     * @see    Charcoal\Source\StorableTrait::save() For the "create" event.
     * @return boolean
     * @todo   Enable model validation.
     */
    public function save()
    {
        $pre = $this->preSave();
        if ($pre === false) {
            return false;
        }

        // Disabled: Invalid models can not be saved.
        if (!!false) {
            $valid = $this->validate();
            if ($valid === false) {
                return false;
            }
        }

        $ret = $this->source()->saveItem($this);
        if ($ret === false) {
            return false;
        } else {
            $this->setId($ret);
        }
        $this->postSave();
        return $ret;
    }

    /**
     * StorableTrait > preSave(). Save hook called before saving the model.
     *
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
     * @return boolean
     */
    protected function preUpdate(array $properties = null)
    {
        return $this->saveProperties($properties);
    }

    /**
     * DescribableTrait > createMetadata().
     *
     * @return MetadataInterface
     */
    protected function createMetadata()
    {
        return new ModelMetadata();
    }

    /**
     * StorableInterface > createSource()
     *
     * @throws UnexpectedValueException If the metadata source can not be found.
     * @return SourceInterface
     */
    protected function createSource()
    {
        $metadata      = $this->metadata();
        $defaultSource = $metadata->defaultSource();
        $sourceConfig  = $metadata->source($defaultSource);

        if (!$sourceConfig) {
            throw new UnexpectedValueException(sprintf(
                'Can not create source for [%s]: invalid metadata.',
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
     * @param array $data Optional.
     * @return ValidatorInterface
     */
    protected function createValidator(array $data = null)
    {
        $validator = new ModelValidator($this);
        if ($data !== null) {
            $validator->setData($data);
        }
        return $validator;
    }

    /**
     * @param array $data Optional. View data.
     * @return ViewInterface
     */
    public function createView(array $data = null)
    {
        $this->logger->warning('Obsolete method createView called.');
        $view = new GenericView([
            'logger' => $this->logger
        ]);
        if ($data !== null) {
            $view->setData($data);
        }
        return $view;
    }

    /**
     * Convert the current class name in "type-ident" format.
     *
     * @return string
     */
    public function objType()
    {
        $ident = preg_replace('/([a-z])([A-Z])/', '$1-$2', get_class($this));
        $objType = strtolower(str_replace('\\', '/', $ident));
        return $objType;
    }
}
