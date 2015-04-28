<?php

namespace Charcoal\Model;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\Model\ModelInterface as ModelInterface;
use \Charcoal\Model\ModelMetadata as ModelMetadata;
use \Charcoal\Model\ModelValidator as ModelValidator;
use \Charcoal\Model\ModelView as ModelView;

use \Charcoal\Metadata\DescribableInterface as DescribableInterface;
use \Charcoal\Metadata\DescribableTrait as DescribableTrait;

use \Charcoal\Source\StorableInterface as StorableInterface;
use \Charcoal\Source\StorableTrait as StorableTrait;

use \Charcoal\Validator\ValidatableInterface as ValidatableInterface;
use \Charcoal\Validator\ValidatableTrait as validatableTrait;

use \Charcoal\View\ViewableInterface as ViewableInterface;
use \Charcoal\View\ViewableTrait as ViewableTrait;

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
    * @param array $data
    */
    public function __construct($data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }
        // Fix bug @todo
        $this->metadata();
    }

    /**
    * ModelInterface > set_data(). Sets the data
    *
    * This function takes an array and fill the object with its value.
    *
    * @param  array $data
    * @throws \InvalidArgumentException if the data parameter is not an array
    * @return ModelInterface Chainable
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'() - Data must be an array');
        }

        foreach ($data as $prop => $val) {
            $this->{$prop} = $val;
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
            // Error. Invalid object? @todo error report
            // @todo Throw exception here?
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
    * @param  array $data
    * @return AbstractModel Chainable
    */
    public function set_flat_data($data)
    {
        if (!is_array($data)) {
            // @todo Log Error
            return $this;
        }

        foreach ($data as $prop => $val) {
            $this->{$prop} = $val;
        }

        // Chainable
        return $this;
    }

    /**
    * @return
    */
    public function flat_data()
    {
        return []; // @todo
    }

    /**
    *
    */
    public function property_value($property_ident)
    {
        $fn = [$this, $property_ident];
        if (is_callable($fn)) {
            return call_user_func($fn);
        } else {
            return isset($this->{$property_ident}) ? $this->{$property_ident} : null;
        }
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
    protected function create_metadata($data = null)
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
    * @param array $data
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
        //$source->set_config($source_config);
        $source->set_table($table);


        if ($data !== null) {
            $source->set_data($data);
        }
        return $source;
    }

    /**
    * ValidatableInterface > create_validator().
    *
    * @return ValidatorInterface
    */
    protected function create_validator($data = null)
    {
        $validator = new ModelValidator($this);
        if ($data !== null) {
            $validator->set_data($data);
        }
        return $validator;
    }

    /**
    * ViewableInterface > create_view().
    *
    * @return ViewInterface
    */
    protected function create_view($data = null)
    {
        $view = new ModelView();
        if ($data !== null) {
            $view->set_data($data);
        }
        return $view;
    }
}
