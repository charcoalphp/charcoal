<?php

namespace Charcoal\Model;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;
use \JsonSerializable;
use \Serializable;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\NullLogger;

// Module `charcoal-config` dependencies
use \Charcoal\Config\AbstractEntity;

// Module `charcoal-view` dependencies
use \Charcoal\View\GenericView;
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

// Intra-module (`charcoal-core`) dependencies
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
    LoggerAwareInterface,
    StorableInterface,
    ValidatableInterface,
    ViewableInterface
{
    use LoggerAwareTrait;
    use DescribableTrait;
    use StorableTrait;
    use ValidatableTrait;
    use ViewableTrait;

    const DEFAULT_SOURCE_TYPE = 'database';

    /**
    * @param array $data Dependencies.
    */
    public function __construct(array $data = null)
    {
        if (!isset($data['logger'])) {
            $data['logger'] = new NullLogger();
        }
        $this->setLogger($data['logger']);

        // Optional DescribableInterface dependencies
        if (isset($data['property_factory'])) {
            $this->setPropertyFactory($data['property_factory']);
        }
        if (isset($data['metadata_loader'])) {
            $this->setMetadataLoader($data['metadata_loader']);
        }
        // Optional StorableInterface dependencies
        if (isset($data['source_factory'])) {
            $this->setSourceFactory($data['source_factory']);
        }
        // Optional ViewableInterface dependencies
        if (isset($data['view'])) {
            var_dump('Cool, view was set '.get_class($this));
            $this->setView($data['view']);
        }

        /** @todo Needs fix. Must be manually triggered after setting data for metadata to work */
        $this->metadata();
    }


    /**
    * Return the object data as an array
    *
    * @param array $filters Optional. Property filter.
    * @return array
    */
    public function data(array $property_filters = null)
    {
        $data = [];
        $properties = $this->properties($property_filters);
        foreach ($properties as $property_ident => $property) {
            // Ensure objects are properly encoded.
            $data[$property_ident] = json_decode(json_encode($property), true);
        }
        return $data;
    }

    /**
    * Sets the data
    *
    * This function takes a 1-dimensional array and fill the object with its value.
    *
    * @param array $data
    * @return AbstractModel Chainable
    */
    public function setFlatData(array $flatData)
    {
        $data = [];
        $properties = $this->properties();
        foreach ($properties as $property_ident => $property) {
            $fields = $property->fields();
            if (count($fields) == 1) {
                $f = $fields[0];
                $f_id = $f->ident();
                if (isset($flatData[$f_id])) {
                    $data[$property_ident] = $flatData[$f_id];
                    unset($flatData[$f_id]);
                }
            } else {
                $p = [];
                foreach ($fields as $f) {
                    $f_id = $f->ident();
                    $key = str_replace($property_ident.'_', '', $f_id);
                    if (isset($flatData[$f_id])) {
                        $data[$property_ident][$key] = $flatData[$f_id];
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
    * @param string $property_ident
    * @return mixed
    */
    public function propertyValue($property_ident)
    {
        $getter = $this->getter($property_ident);
        $func   = [ $this, $getter ];

        if (is_callable($func)) {
            return call_user_func($func);
        } elseif (isset($this->{$property_ident})) {
            return $this->{$property_ident};
        }

        return null;
    }

    /**
    * @param array $properties
    * @return boolean
    */
    public function saveProperties(array $properties = null)
    {
        if ($properties === null) {
            $properties = array_keys($this->metadata()->properties());
        }

        foreach ($properties as $property_ident) {
            $p = $this->p($property_ident);
            $p->save();

            if ($p->val() === null) {
                continue;
            }

            $this->setData([
                $property_ident => $p->val()
            ]);
        }

        return true;
    }
    /**
    * StorableTrait > save(). Save an object current state to storage
    *
    * @return boolean
    */
    public function save()
    {
        $pre = $this->preSave();
        if ($pre === false) {
            return false;
        }

        $this->saveProperties();

        // Invalid models can not be saved.
        // $valid = $this->validate();
        // if ($valid === false) {
        //     return false;
        // }

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
    * @param array $properties
    * @return mixed
    */
    public function update(array $properties = null)
    {
        $pre = $this->preUpdate();
        if ($pre === false) {
            return false;
        }

        $this->saveProperties();

        // $valid = $this->validate();
        // if ($valid === false) {
        //     return false;
        // }

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
    * @param array $properties
    * @return boolean
    */
    protected function preUpdate(array $properties = null)
    {
        return true;
    }

    /**
    * StorableTrait > postUpdate(). Update hook called after updating the model.
    *
    * @param array $properties
    * @return boolean
    */
    protected function postUpdate($properties = null)
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
    * @param array $data Optional
    * @throws Exception If the metadata source can not be found.
    * @return SourceInterface
    */
    protected function createSource($data = null)
    {
        $metadata = $this->metadata();
        $defaultSource = $metadata->defaultSource();
        $sourceConfig = $metadata->source($defaultSource);

        if (!$sourceConfig) {
            throw new Exception(
                sprintf('Can not create %s source: invalid metadata.', get_class($this))
            );
        }

        $source_type = isset($sourceConfig['type']) ? $sourceConfig['type'] : self::DEFAULT_SOURCE_TYPE;
        $source_factory = $this->sourceFactory();
        $source = $source_factory->create($source_type, [
            'logger'=>$this->logger
        ]);
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
    * @param array $data Optional
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
    * @param array $data
    * @return ViewInterface
    */
    public function createView(array $data = null)
    {
        $view = new GenericView([
            'logger'=>$this->logger
        ]);
        if ($data !== null) {
            $view->setData($data);
        }
        return $view;
    }

    /**
    * Convert the current class name in
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
