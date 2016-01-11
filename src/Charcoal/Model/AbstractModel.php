<?php

namespace Charcoal\Model;

// Dependencies from `PHP`
use \InvalidArgumentException;
use \JsonSerializable;
use \Serializable;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Charcoal;
use \Charcoal\Model\DescribableInterface;
use \Charcoal\Model\DescribableTrait;
use \Charcoal\Source\SourceFactory;
use \Charcoal\Source\StorableInterface;
use \Charcoal\Source\StorableTrait;
use \Charcoal\Validator\ValidatableInterface;
use \Charcoal\Validator\ValidatableTrait;
use \Charcoal\View\GenericView;
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

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
abstract class AbstractModel implements
    JsonSerializable,
    Serializable,
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
        if (isset($data['logger'])) {
            $this->setLogger($data['logger']);
        }

        /** @todo Needs fix. Must be manually triggered after setting data for metadata to work */
        $this->metadata();
    }

    /**
    * ModelInterface > set_data(). Sets the data
    *
    * This function takes an array and fill the model object with its value.
    *
    * This method either calls a setter for each key (`set_{$key}()`) or sets a public member.
    *
    * For example, calling with `set_data(['properties'=>$properties])` would call
    *`set_properties($properties)`, becasue `set_properties()` exists.
    *
    * But calling with `set_data(['foobar'=>$foo])` would set the `$foobar` member
    * on the metadata object, because the method `set_foobar()` does not exist.
    *
    *
    * @param array $data
    * @return AbstractModel Chainable
    */
    public function set_data(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, 'set_'.$prop];
            if (is_callable($func)) {
                call_user_func($func, $val);
                unset($data[$prop]);
            } else {
                $this->{$prop} = $val;
            }
        }

        // Chainable
        return $this;
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
    public function set_flat_data(array $flat_data)
    {
        $data = [];
        $properties = $this->properties();
        foreach ($properties as $property_ident => $property) {
            $fields = $property->fields();
            if (count($fields) == 1) {
                $f = $fields[0];
                $f_id = $f->ident();
                if (isset($flat_data[$f_id])) {
                    $data[$property_ident] = $flat_data[$f_id];
                    unset($flat_data[$f_id]);
                }
            } else {
                $p = [];
                foreach ($fields as $f) {
                    $f_id = $f->ident();
                    $key = str_replace($property_ident.'_', '', $f_id);
                    if (isset($flat_data[$f_id])) {
                        $data[$property_ident][$key] = $flat_data[$f_id];
                        unset($flat_data[$f_id]);
                    }
                }
            }
        }
        $this->set_data($data);
        if (!empty($flat_data)) {
            $this->set_data($flat_data);
        }
        return $this;
    }

    /**
    * @return array
    * @todo Implement retrieval of flattened data
    */
    public function flat_data()
    {
        return [];
    }

    /**
    * @param string $property_ident
    * @return mixed
    */
    public function property_value($property_ident)
    {
        $fn = [$this, $property_ident];
        if (is_callable($fn)) {
            return call_user_func($fn);
        } else {
            return (isset($this->{$property_ident}) ? $this->{$property_ident} : null);
        }
    }

    /**
    * @param array $properties
    * @return boolean
    */
    public function save_properties(array $properties = null)
    {
        if ($properties===null) {
            $properties = array_keys($this->metadata()->properties());
        }
        foreach ($properties as $property_ident) {
            $p = $this->p($property_ident);
            $p->save();

            if ($p->val() === null) {
                continue;
            }

            $this->set_data([
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
        $pre = $this->pre_save();
        if ($pre === false) {
            return false;
        }

        $this->save_properties();

        // Invalid models can not be saved.
        // $valid = $this->validate();
        // if ($valid === false) {
        //     return false;
        // }

        $ret = $this->source()->save_item($this);
        if ($ret === false) {
            return false;
        } else {
            $this->set_id($ret);
        }
        $this->post_save();
        return $ret;
    }

    /**
    * @param array $properties
    * @return mixed
    */
    public function update($properties = null)
    {
        $pre = $this->pre_update();
        if ($pre === false) {
            return false;
        }

        $this->save_properties();

        // $valid = $this->validate();
        // if ($valid === false) {
        //     return false;
        // }

        $ret = $this->source()->update_item($this, $properties);
        if ($ret === false) {
            return false;
        }

        $this->post_update();
        return $ret;
    }

    /**
    * StorableTrait > pre_save(). Save hook called before saving the model.
    *
    * @return boolean
    */
    protected function pre_save()
    {
        return true;
    }

    /**
    * StorableTrait > post_save(). Save hook called after saving the model.
    *
    * @return boolean
    */
    protected function post_save()
    {
        return true;
    }

    /**
    * StorableTrait > pre_update(). Update hook called before updating the model.
    *
    * @param array $properties
    * @return boolean
    */
    protected function pre_update($properties = null)
    {
        return true;
    }

    /**
    * StorableTrait > post_update(). Update hook called after updating the model.
    *
    * @param array $properties
    * @return boolean
    */
    protected function post_update($properties = null)
    {
        return true;
    }

    /**
    * StorableTrait > pre_delete(). Delete hook called before deleting the model.
    *
    * @return boolean
    */
    protected function pre_delete()
    {
        return true;
    }

    /**
    * StorableTrait > post_delete(). Delete hook called after deleting the model.
    *
    * @return boolean
    */
    protected function post_delete()
    {
        return true;
    }

    /**
    * DescribableTrait > create_metadata().
    *
    * @param array $data Optional data to intialize the Metadata object with.
    * @return MetadataInterface
    */
    protected function create_metadata(array $data = null)
    {
        $metadata = new ModelMetadata();
        if ($data !== null) {
            $metadata->set_data($data);
        }
        return $metadata;
    }

    /**
    * StorableInterface > create_source()
    *
    * @param array $data Optional
    * @return SourceInterface
    */
    protected function create_source($data = null)
    {
        $metadata = $this->metadata();
        $default_source = $metadata->default_source();
        $source_config = $metadata->source($default_source);

        $source_type = isset($source_config['type']) ? $source_config['type'] : self::DEFAULT_SOURCE_TYPE;

        $source_factory = new SourceFactory();
        $source = $source_factory->create($source_type, [
            'logger'=>$this->logger
        ]);
        $source->set_model($this);

        if ($data !== null) {
            $data = array_merge_recursive($source_config, $data);
        } else {
            $data = $source_config;
        }
        $source->set_data($data);

        return $source;
    }

    /**
    * ValidatableInterface > create_validator().
    *
    * @param array $data Optional
    * @return ValidatorInterface
    */
    protected function create_validator(array $data = null)
    {
        $validator = new ModelValidator($this);
        if ($data !== null) {
            $validator->set_data($data);
        }
        return $validator;
    }

    /**
    * @param array $data
    * @return ViewInterface
    */
    public function create_view(array $data = null)
    {
        $view = new GenericView([
            'logger'=>$this->logger
        ]);
        if ($data !== null) {
            $view->set_data($data);
        }
        return $view;
    }

    /**
    * Serializable > serialize()
    */
    public function serialize()
    {
        $data = $this->data();
        return serialize($data);
    }

    /**
    * Serializable > unsierialize()
    *
    * @param string $data Serialized data
    * @return void
    */
    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->set_data($data);
    }

    /**
    * JsonSerializable > jsonSerialize()
    */
    public function jsonSerialize()
    {
        return $this->data();
    }

    /**
    * Convert the current class name in
    *
    * @return string
    */
    public function obj_type()
    {
        $classname = get_class($this);
        $ident = preg_replace('/(^\\[A-Z])/', '-${1}', $classname);
        $obj_type = strtolower(str_replace('\\', '/', $ident));
        return $obj_type;
    }
}
