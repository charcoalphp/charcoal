<?php

namespace Charcoal\Model;

use \Exception;
use \InvalidArgumentException;
use \JsonSerializable;
use \Serializable;

// Dependencies from PSR-3 (Logger)
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\NullLogger;

use \Pimple\Container;

// Dependency from 'charcoal-config'
use \Charcoal\Config\AbstractEntity;

// Dependencies from 'charcoal-view'
use \Charcoal\View\GenericView;
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

// Dependencies from 'charcoal-property'
use \Charcoal\Property\DescribablePropertyInterface;
use \Charcoal\Property\DescribablePropertyTrait;

// Intra-module ('charcoal-core') dependencies
use \Charcoal\Model\DescribableInterface;
use \Charcoal\Model\DescribableTrait;
use \Charcoal\Source\SourceFactory;
use \Charcoal\Source\StorableInterface;
use \Charcoal\Source\StorableTrait;
use \Charcoal\Validator\ValidatableInterface;
use \Charcoal\Validator\ValidatableTrait;

// Local namespace dependencies
use \Charcoal\Model\ModelInterface;
use \Charcoal\Model\ModelMetadata;
use \Charcoal\Model\ModelValidator;

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
 * Those interfaces
 * are implemented (in parts, at least) with the `DescribableTrait`, `StorableTrait`,
 * `ValidatableTrait` and the `ViewableTrait`.
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
     * @param Container $container A dependencies container instance.
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
     * @param array|\Traversable $data The object data.
     * @return array|\Traversable The object data without the pre-set ID.
     */
    public function setIdFromData($data)
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
     * @param array|\Traversable $data The entity data. Will call setters.
     * @return AbstractModel Chainable
     */
    public function setData($data)
    {
        $data = $this->setIdFromData($data);

        return parent::setData($data);
    }

    /**
     * Return the object data as an array.
     *
     * @param array $propertyFilters Optional. Property filter.
     * @return array
     */
    public function data(array $propertyFilters = null)
    {
        $data = [];
        $properties = $this->properties($propertyFilters);
        foreach ($properties as $propertyIdent => $property) {
            // Ensure objects are properly encoded.
            $data[$propertyIdent] = json_decode(json_encode($property), true);
        }
        return $data;
    }

    /**
     * Override's `\Charcoal\Config\AbstractEntity`'s `setData` method to take properties into consideration.
     *
     * Also add a special case, to merge values for l10n properties.
     *
     * @param array|\Traversable $data The data to merge.
     * @return EntityInterface Chainable
     * @see \Charcoal\Config\AbstractEntity::offsetSet()
     */
    public function mergeData($data)
    {
        $data = $this->setIdFromData($data);

        foreach ($data as $propIdent => $val) {
            if (!$this->hasProperty($propIdent)) {
                $this->logger->warning(
                    sprintf('Cannot set property "%s" on object; not defined in metadata.', $propIdent)
                );
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
     * @return array
     */
    public function defaultData()
    {
        $metadata = $this->metadata();
        return $metadata->defaultData();
    }

    /**
     * Sets the data
     *
     * This function takes a 1-dimensional array and fill the object with its value.
     *
     * @param array $flatData The data, as a flat (1-dimension) array.
     * @return AbstractModel Chainable
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
                    $f_id = $f->ident();
                    $key = str_replace($propertyIdent.'_', '', $f_id);
                    if (isset($flatData[$f_id])) {
                        $data[$propertyIdent][$key] = $flatData[$f_id];
                        unset($flatData[$f_id]);
                    }
                } else {
                    $f_id = $f->ident();
                    if (isset($flatData[$f_id])) {
                        $data[$propertyIdent] = $flatData[$f_id];
                        unset($flatData[$f_id]);
                    }
                }
            }
        }

        $this->setData($data);
        if (!empty($flatData)) {
            $this->setData($flatData);
        }

        return $this;
    }

    /**
     * @return array
     * @todo Implement retrieval of flattened data
     */
    public function flatData()
    {
        return [];
    }

    /**
     * @param string $propertyIdent The property ident to get the value from.
     * @return mixed
     */
    public function propertyValue($propertyIdent)
    {
        $getter = $this->getter($propertyIdent);
        $func   = [ $this, $getter ];

        if (is_callable($func)) {
            return call_user_func($func);
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
     * @param string $key   Key pointing a column's l10n base ident.
     * @param mixed  $value Value to search in all languages.
     * @param array  $langs List of languages (code, ex: "en") to check into.
     * @throws Exception If the PDO query fails.
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
            throw new Exception('Error');
        }

        $data = $sth->fetch(\PDO::FETCH_ASSOC);

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

        $this->saveProperties();

        // Invalid models can not be saved.
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
     * Update the object in storage with the current state.
     *
     * @see    Charcoal\Source\StorableTrait::update() For the "update" event.
     * @param  array $properties Optional. The list of properties to update.
     * @return mixed
     * @todo   Enable model validation.
     */
    public function update(array $properties = null)
    {
        $pre = $this->preUpdate($properties);
        if ($pre === false) {
            return false;
        }

        $this->saveProperties();

        // Invalid models can not be saved.
        if (!!false) {
            $valid = $this->validate();
            if ($valid === false) {
                return false;
            }
        }

        $ret = $this->source()->updateItem($this, $properties);
        if ($ret === false) {
            return false;
        }

        $this->postUpdate();
        return $ret;
    }

    /**
     * StorableTrait > preSave(). Save hook called before saving the model.
     *
     * @return boolean
     */
    protected function preSave()
    {
        return true;
    }

    /**
     * StorableTrait > postSave(). Save hook called after saving the model.
     *
     * @return boolean
     */
    protected function postSave()
    {
        return true;
    }

    /**
     * StorableTrait > preUpdate(). Update hook called before updating the model.
     *
     * @param string[] $properties Optional. The properties to update.
     * @return boolean
     */
    protected function preUpdate(array $properties = null)
    {
        return true;
    }

    /**
     * StorableTrait > postUpdate(). Update hook called after updating the model.
     *
     * @param string[] $properties Optional. The properties to update.
     * @return boolean
     */
    protected function postUpdate(array $properties = null)
    {
        return true;
    }

    /**
     * StorableTrait > preDelete(). Delete hook called before deleting the model.
     *
     * @return boolean
     */
    protected function preDelete()
    {
        return true;
    }

    /**
     * StorableTrait > postDelete(). Delete hook called after deleting the model.
     *
     * @return boolean
     */
    protected function postDelete()
    {
        return true;
    }

    /**
     * DescribableTrait > create_metadata().
     *
     * @param array $data Optional data to intialize the Metadata object with.
     * @return MetadataInterface
     */
    protected function createMetadata(array $data = null)
    {
        $metadata = new ModelMetadata();
        if ($data !== null) {
            $metadata->setData($data);
        }
        return $metadata;
    }

    /**
     * StorableInterface > createSource()
     *
     * @param array $data Optional source data.
     * @throws Exception If the metadata source can not be found.
     * @return SourceInterface
     */
    protected function createSource(array $data = null)
    {
        $metadata = $this->metadata();
        $defaultSource = $metadata->defaultSource();
        $sourceConfig = $metadata->source($defaultSource);

        if (!$sourceConfig) {
            throw new Exception(
                sprintf('Can not create %s source: invalid metadata.', get_class($this))
            );
        }

        $sourceType = isset($sourceConfig['type']) ? $sourceConfig['type'] : self::DEFAULT_SOURCE_TYPE;
        $sourceFactory = $this->sourceFactory();
        $source = $sourceFactory->create($sourceType);
        $source->setModel($this);

        if ($data !== null) {
            $data = array_merge_recursive($sourceConfig, $data);
        } else {
            $data = $sourceConfig;
        }

        $source->setData($data);

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
            'logger'=>$this->logger
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
