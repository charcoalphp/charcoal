<?php

namespace Charcoal\Model;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Metadata\DescribableInterface as DescribableInterface;
use \Charcoal\Metadata\DescribableTrait as DescribableTrait;
use \Charcoal\Source\StorableInterface as StorableInterface;
use \Charcoal\Source\StorableTrait as StorableTrait;
use \Charcoal\Validator\ValidatableInterface as ValidatableInterface;
use \Charcoal\Validator\ValidatableTrait as validatableTrait;
use \Charcoal\View\ViewableInterface as ViewableInterface;
use \Charcoal\View\ViewableTrait as ViewableTrait;

// Local namespace dependencies
use \Charcoal\Model\ModelInterface as ModelInterface;
use \Charcoal\Model\ModelMetadata as ModelMetadata;
use \Charcoal\Model\ModelValidator as ModelValidator;
use \Charcoal\Model\ModelView as ModelView;

/**
* An abstract class that implements most of `ModelInterface`.
*
* In addition to `ModelInterface`, the abstract model implements the `DescribableInterface`,
* `StorableInterface, `ValidatableInterface` and the `ViewableInterface`. Those interfaces
* are implemented (in parts, at least) with the `DescribableTrait`, `StorableTrait`,
* `ValidatableTrait` and the `ViewableTrait`.
*/
abstract class AbstractModel implements
    ModelInterface,
    DescribableInterface,
    StorableInterface,
    ValidatableInterface,
    ViewableInterface
{
    use DescribableTrait;
    use StorableTrait;
    use ValidatableTrait;
    use ViewableTrait;

    /**
    * @param array $data Optional
    */
    public function __construct(array $data = null)
    {
        if (is_array($data)) {
            $this->set_data($data);
        }
        /** @todo Needs fix. Must be manually triggered after setting data for metadata to work */
        $this->metadata();
    }

    /**
    * ModelInterface > set_data(). Sets the data
    *
    * This function takes an array and fill the object with its value.
    *
    * @param array $data
    * @return AbstractModel Chainable
    */
    public function set_data(array $data)
    {
        $this->set_describable_data($data);
        $this->set_storable_data($data);
        $this->set_viewable_data($data);
        foreach ($data as $prop => $val) {
            $func = [$this, 'set_'.$prop];
            if (is_callable($func)) {
                call_user_func($func, $val);
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
    * @return array
    */
    public function data()
    {
        // Return value is array
        $data = [];

        $metadata = $this->metadata();
        $props = $metadata['properties'];

        if (!is_array($props)) {
            /**
            * @todo Error. Invalid object? Report error or throw exception?
            */
            return false;
        }

        foreach ($props as $property_ident => $property_options) {
            $p = $this->p($property_ident);

            if (!$p instanceof PropertyInterface) {
                continue;
            }
            $data[$property_ident] = $this->p($property_ident)->val();
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
    public function set_flat_data(array $data)
    {
        return $this->set_data($data);
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
    public function save_properties($properties = null)
    {
        if ($properties===null) {
            $properties = array_keys($this->metadata()->properties());
        }
        foreach ($properties as $property_ident) {
            $p = $this->p($property_ident);
            $p->save();
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
        $valid = $this->validate();
        if ($valid === false) {
            return false;
        }

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
        if (is_array($data)) {
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
        $table = $source_config['table'];

        $source = new \Charcoal\Source\DatabaseSource();
        $source->set_model($this);
        // $source->set_config($source_config);
        $source->set_table($table);

        if (is_array($data)) {
            $source->set_data($data);
        }
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
        if (is_array($data)) {
            $validator->set_data($data);
        }
        return $validator;
    }

    /**
    * ViewableInterface > create_view().
    *
    * @param array $data Optional
    * @return ViewInterface
    */
    protected function create_view(array $data = null)
    {
        $view = new ModelView();
        if (is_array($data)) {
            $view->set_data($data);
        }
        return $view;
    }
}
