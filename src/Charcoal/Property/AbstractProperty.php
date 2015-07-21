<?php

namespace Charcoal\Property;

// Dependencies from `PHP`
use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Metadata\DescribableInterface as DescribableInterface;
use \Charcoal\Metadata\DescribableTrait as DescribableTrait;
use \Charcoal\Validator\ValidatableInterface as ValidatableInterface;
use \Charcoal\Validator\ValidatableTrait as validatableTrait;
use \Charcoal\View\ViewableInterface as ViewableInterface;
use \Charcoal\View\ViewableTrait as ViewableTrait;

// Local namespace dependencies
use \Charcoal\Property\PropertyInterface as PropertyInterface;
use \Charcoal\Property\PropertyValidator as PropertyValidator;
use \Charcoal\Property\PropertyView as PropertyView;

/**
* An abstract class that implements the full `PropertyInterface`.
*/
abstract class AbstractProperty implements
    PropertyInterface,
    DescribableInterface,
    ValidatableInterface,
    ViewableInterface
{
    use DescribableTrait;
    use ValidatableTrait;
    use ViewableTrait;

    /**
    * @var string $_ident
    */
    private $_ident;

    /**
    * @var mixed $_val
    */
    protected $_val;

    /**
    * @var mixed $_label
    */
    private $_label;

    /**
    * @var boolean $l10n
    */
    private $_l10n = false;

    /**
    * @var boolean $hidden;
    */
    private $_hidden = false;

    /**
    * @var boolean $multiple
    */
    private $_multiple = false;

    /**
    * Array of options for multiple properties
    * - `separator` (default=",") How the values will be separated in the storage (sql)
    * - `min` (default=null) The minimum number of values. If null, <0 or NaN, then this is not taken into consideration
    * - `max` (default=null) The maximum number of values. If null, <0 or NaN, then there is not limit
    * @var mixed $multiple_options
    */
    private $_multiple_options;

    /**
    * If true, this property *must* have a value
    * @var boolean $required
    */
    private $_required = false;

    /**
    * Unique properties should not share he same value across 2 objects
    * @var boolean $unique
    */
    private $_unique = false;

    /**
    * @var boolean $_allow_null
    */
    private $_allow_null = true;

    /**
    * Inactive properties should be hidden everywhere / unused
    * @var boolean $active
    */
    private $_active = true;

    /**
    * @param array $data Optional
    */
    public function __construct(array $data = null)
    {
        // Set default values
        $defaults = [
            'ident'    => '',
            'l10n'     => false,
            'hidden'   => false,
            'multiple' => false,
            'required' => false,
            'unique'   => false,
            'active'   => true
        ];

        if ($data === null) {
            $data = $defaults;
        } elseif (is_array($data)) {
            $data = array_merge($defaluts, $data);
        }

        $this->set_data($data);
    }

    /**
    * @return string
    */
    public function __toString()
    {
        $val = $this->val();
        if (is_string($val)) {
            return $val;
        } else {
            return '';
        }
    }

    /**
    * @param array $data
    * @return AbstractProperty Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['val'])) {
            $this->set_val($data['val']);
        }
        if (isset($data['label'])) {
            $this->set_label($data['label']);
        }
        if (isset($data['l10n'])) {
            $this->set_l10n($data['l10n']);
        }
        if (isset($data['hidden'])) {
            $this->set_hidden($data['hidden']);
        }
        if (isset($data['multiple'])) {
            $this->set_multiple($data['multiple']);
        }
        if (isset($data['multiple_options'])) {
            $this->set_multiple_options($data['multiple_options']);
        }
        if (isset($data['required'])) {
            $this->set_required($data['required']);
        }
        if (isset($data['unique'])) {
            $this->set_unique($data['unique']);
        }
        if (isset($data['active'])) {
            $this->set_active($data['active']);
        }

        return $this;
    }

    /**
    * @return string
    */
    abstract public function type();

    /**
    * @param string $ident
    * @throws InvalidArgumentException  if ident is not a string
    * @return AbstractProperty Chainable
    */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException('Ident needs to be string.');
        }
        $this->_ident = $ident;
        return $this;
    }

    /**
    * @throws Exception if trying to access getter before setter
    * @return string
    */
    public function ident()
    {
        if ($this->_ident === null) {
            throw new Exception('Ident was never set.');
        }
        return $this->_ident;
    }

    /**
    * @param mixed $val
    * @return Property (Chainable)
    */
    public function set_val($val)
    {
        $this->_val = $val;
        return $this;
    }

    /**
    * @return mixed
    */
    public function val()
    {
        return $this->_val;
    }

    /**
    * @param string $field_ident
    * @throws Exception if the value is not an array (therefore no field should be defined)
    * @return mixed
    */
    public function field_val($field_ident)
    {
        $val = $this->val();
        if ($val === null) {
            return null;
        }
        if (!is_array($val)) {
            return $this->storage_val($val);
        }
        if (isset($val[$field_ident])) {
            return $this->storage_val($val[$field_ident]);
        } else {
            return null;
        }
    }

    /**
    * @param mixed $val
    * @return mixed
    */
    public function storage_val($val = null)
    {
        if ($val === null) {
            $val = $this->val();
        }
        if ($val === null) {
            return null;
        }
        if (!is_scalar($val)) {
            return json_encode($val, true);
        }
        return $val;
    }

    /**
    * @param mixed $label
    * @return Property (Chainable)
    */
    public function set_label($label)
    {
        $this->_label = $label;
        return $this;
    }

    /**
    * @return boolean
    */
    public function label()
    {
        if ($this->_label === null) {
            return ucwords(str_replace(['.', '_'], ' ', $this->ident()));
        }
        return $this->_label;
    }

    /**
    * @param boolean $l10n
    * @throws InvalidArgumentException if the paramter is not a boolean
    * @return Property (Chainable)
    */
    public function set_l10n($l10n)
    {
        if (!is_bool($l10n)) {
            throw new InvalidArgumentException('l10n must be a boolean.');
        }
        $this->_l10n = $l10n;
        return $this;
    }

    /**
    * @return boolean
    */
    public function l10n()
    {
        return $this->_l10n;
    }

    /**
    * @param boolean $hidden
    * @throws InvalidArgumentException if the paramter is not a boolean
    * @return Property (Chainable)
    */
    public function set_hidden($hidden)
    {
        if (!is_bool($hidden)) {
            throw new InvalidArgumentException('hidden must be a boolean.');
        }
        $this->hidden = $hidden;
        return $this;
    }

    /**
    * @return boolean
    */
    public function hidden()
    {
        return !!$this->hidden;
    }

    /**
    * @param boolean $multiple
    * @throws InvalidArgumentException if the paramter is not a boolean
    * @return Property (Chainable)
    */
    public function set_multiple($multiple)
    {
        if (!is_bool($multiple)) {
            throw new InvalidArgumentException('multiple must be a boolean.');
        }
        $this->multiple = $multiple;
        return $this;
    }

    /**
    * @return boolean
    */
    public function multiple()
    {
        return !!$this->multiple;
    }

    /**
    * @param boolean $allow
    * @throws InvalidArgumentException if the paramter is not a boolean
    * @return Property (Chainable)
    */
    public function set_allow_null($allow)
    {
        if (!is_bool($allow)) {
            throw new InvalidArgumentException('Allow null must be a boolean.');
        }
        $this->_allow_null = $allow;
        return $this;
    }

    /**
    * @return boolean
    */
    public function allow_null()
    {
        return !!$this->_allow_null;
    }

    /**
    * @param array $multiple_options
    * @throws InvalidArgumentException if the paramter is not an array
    * @return Property (Chainable)
    */
    public function set_multiple_options(array $multiple_options)
    {
        $default_options = [
            'separator' => ',',
            'min'       => 0,
            'max'       => 0
        ];
        $options = array_merge($default_options, $multiple_options);
        $this->multiple_options = $options;
        return $this;
    }

    /**
    * @return array
    */
    public function multiple_options()
    {
        return $this->multiple_options;
    }

    /**
    * @param boolean $required
    * @throws InvalidArgumentException if the paramter is not a boolean
    * @return Property (Chainable)
    */
    public function set_required($required)
    {
        if (!is_bool($required)) {
            throw new InvalidArgumentException('Required must be a boolean.');
        }
        $this->_required = $required;
        return $this;
    }

    /**
    * @return boolean
    */
    public function required()
    {
        return !!$this->_required;
    }

    /**
    * @param boolean $unique
    * @throws InvalidArgumentException if the paramter is not a boolean
    * @return Property (Chainable)
    */
    public function set_unique($unique)
    {
        if (!is_bool($unique)) {
            throw new InvalidArgumentException('Unique must be a boolean.');
        }
        $this->unique = $unique;
        return $this;
    }

    /**
    * @return boolean
    */
    public function unique()
    {
        return !!$this->unique;
    }

    /**
    * @param boolean $active
    * @throws InvalidArgumentException if the paramter is not a boolean
    * @return Property (Chainable)
    */
    public function set_active($active)
    {
        if (!is_bool($active)) {
            throw new InvalidArgumentException('Active must be a boolean.');
        }
        $this->active = $active;
        return $this;
    }

    /**
    * @return boolean
    */
    public function active()
    {
        return !!$this->active;
    }

    /**
    * @return array
    */
    public function fields()
    {
        $fields = [];
        if ($this->l10n()) {
            $langs = ['fr', 'en']; /** @todo Implement retrieval of active languages */
            foreach ($langs as $lang) {
                $field = new PropertyField();
                $field->set_data(
                    [
                        'ident'        => $this->ident().'_'.$lang,
                        'sql_type'     => $this->sql_type(),
                        'sql_pdo_type' => $this->sql_pdo_type(),
                        'extra'        => $this->sql_extra(),
                        'val'          => $this->field_val($lang),
                        'default_val'  => null,
                        'allow_null'   => $this->allow_null(),
                        'comment'      => $this->label()
                    ]
                );
                $fields[$lang] = $field;
            }
        } else {
            $field = new PropertyField();
            $field->set_data(
                [
                    'ident'        => $this->ident(),
                    'sql_type'     => $this->sql_type(),
                    'sql_pdo_type' => $this->sql_pdo_type(),
                    'extra'        => $this->sql_extra(),
                    'val'          => $this->storage_val(),
                    'default_val'  => null,
                    'allow_null'   => $this->allow_null(),
                    'comment'      => $this->label()
                ]
            );
            $fields[] = $field;
        }

        return $fields;
    }

    /**
    * @return array
    */
    public function validation_methods()
    {
        return ['required', 'unique', 'allow_null'];
    }

    /**
    * @return boolean
    */
    public function validate_required()
    {
        if ($this->required() && !$this->val()) {
            $this->validator()->error('Value is required.', 'required');
            return false;
        }

        return true;
    }

    /**
    * @return boolean
    */
    public function validate_unique()
    {
        if (!$this->unique()) {
            return true;
        }

        /** @todo Check in the model's storage if the value already exists. */
        return true;
    }

    /**
    * @return boolean
    */
    public function validate_allow_null()
    {
        if (!$this->allow_null() && $this->val() === null) {
            $this->validator()->error('Value can not be null.', 'allow_null');
            return false;
        }
        return true;
    }

    /**
    * @param string $property_ident
    * @return mixed
    */
    protected function property_value($property_ident)
    {
        return isset($this->{$property_ident}) ? $this->{$property_ident} : null;
    }

    /**
    * @param array $data Optional
    * @return PropertyMetadata
    */
    protected function create_metadata(array $data = null)
    {
        $metadata = new PropertyMetadata();
        if (is_array($data)) {
            $metadata->set_data($data);
        }
        return $metadata;
    }

    /**
    * ValidatableTrait > create_validator(). Create a Validator object
    *
    * @return ValidatorInterface
    */
    protected function create_validator()
    {
        $validator = new PropertyValidator($this);
        return $validator;
    }

    /**
    * ViewableTrait > create_validator(). Create a View object
    *
    * @param array $data Optional
    * @return ViewInterface
    */
    protected function create_view(array $data = null)
    {
        $view = new PropertyView();
        if (is_array($data)) {
            $view->set_data($data);
        }
        return $view;
    }

    /**
    * @return string
    */
    abstract public function sql_extra();

    /**
    * @return string
    */
    abstract public function sql_type();

    /**
    * @return integer
    */
    abstract public function sql_pdo_type();

    /**
    * @return mixed
    */
    abstract public function save();
}
