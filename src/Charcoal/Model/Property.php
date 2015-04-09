<?php

namespace Charcoal\Model;

use \Charcoal\Model\Validator\PropertyValidator as PropertyValidator;
use \Charcoal\Model\Property\Field as Field;
use \Charcoal\Model\View as View;
use \Charcoal\Model\ViewController as ViewController;
use \Charcoal\Validator\ValidatorInterface as ValidatorInterface;

class Property extends Model
{
    /**
    * @var string $_ident
    */
    private $_ident;
    /**
    * @var mixed $_val
    */
    private $_val;

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
    * Inactive properties should be hidden everywhere / unused
    * @var boolean $active
    */
    private $_active = true;


    /**
    *
    */
    public function __construct($metadata_name=null)
    {
        // Set default values
        $this->set_ident('');
        $this->set_l10n(false);
        $this->set_hidden(false);
        $this->set_multiple(false);
        $this->set_required(false);
        $this->set_unique(false);
        $this->set_active(true);

        // Model Constructor
        parent::__construct($metadata_name);


    }

    /**
    *
    */
    public function __toString()
    {
        $val = $this->val();
        if(is_string($val)) {
            return $val;
        }
        else {
            return '';
        }
    }

    /**
    * This should be the main (and only) way to create new Property_* object
    *
    * @param string
    * @param array
    *
    * @return \Charcoal\Property
    */
    final static public function get($type='')
    {
        $class_name = '\Charcoal\Property\\'.str_replace('_', '\\', $type);
        if(class_exists($class_name)) {
            return new $class_name();
        }
        else {
            return new \Charcoal\Model\Property();
        }

    }

    /**
    * @param array $data
    * @throws \InvalidArgumentException if the data parameter is not an array
    * @return Property Chainable
    */
    public function set_data($data)
    {
        if(!is_array($data)) {
            throw new \InvalidArgumentException('Data must be an array');
        }

        //parent::set_data($data);

        if(isset($data['val'])) {
            $this->set_val($data['val']);
        }
        if(isset($data['label'])) {
            $this->set_label($data['label']);
        }
        if(isset($data['l10n'])) {
            $this->set_l10n($data['l10n']);
        }
        if(isset($data['hidden'])) {
            $this->set_hidden($data['hidden']);
        }
        if(isset($data['multiple'])) {
            $this->set_multiple($data['multiple']);
        }
        if(isset($data['multiple_options'])) {
            $this->set_multiple_options($data['multiple_options']);
        }
        if(isset($data['required'])) {
            $this->set_required($data['required']);
        }
        if(isset($data['unique'])) {
            $this->set_unique($data['unique']);
        }
        if(isset($data['active'])) {
            $this->set_active($data['active']);
        }

        return $this;
    }

    public function set_ident($ident)
    {
        if(!is_string($ident)) {
            throw new \InvalidArgumentException('Ident needs to be string');
        }
        $this->_ident = $ident;
        return $this;
    }

    public function ident()
    {
        if($this->_ident === null) {
            throw new \Exception('Ident was never set');
        }
        return $this->_ident;
    }

    /**
    * @param mixed
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

    public function field_val($field_ident)
    {
        $val = $this->val();
        if($val === null) {
            return null;
        }
        if(!is_array($val)) {
            throw new Exception('Val is not an array');
        }
        if(isset($val[$field_ident])) {
            return $val[$field_ident];
        }
        else {
            return null;
        }
    }

    /**
    * @param mixed $label
    * @throws \InvalidArgumentException if the paramter is not a boolean
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
        return $this->_label;
    }

    /**
    * @param boolean
    * @throws \InvalidArgumentException if the paramter is not a boolean
    * @return Property (Chainable)
    */
    public function set_l10n($l10n)
    {
        if(!is_bool($l10n)) {
            throw new \InvalidArgumentException('l10n must be a boolean');
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
    * @param boolean
    * @throws \InvalidArgumentException if the paramter is not a boolean
    * @return Property (Chainable)
    */
    public function set_hidden($hidden)
    {
        if(!is_bool($hidden)) {
            throw new \InvalidArgumentException('hidden must be a boolean');
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
    * @param boolean
    * @throws \InvalidArgumentException if the paramter is not a boolean
    * @return Property (Chainable)
    */
    public function set_multiple($multiple)
    {
        if(!is_bool($multiple)) {
            throw new \InvalidArgumentException('multiple must be a boolean');
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
    * @param array
    * @throws \InvalidArgumentException if the paramter is not an array
    * @return Property (Chainable)
    */
    public function set_multiple_options($multiple_options)
    {
        if(!is_array($multiple_options)) {
            throw new \InvalidArgumentException('multiple options must be an array');
        }
        $default_options = [
        'separator'    => ',',
        'min'        => 0,
        'max'        => 0
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
    * @param boolean
    * @throws \InvalidArgumentException if the paramter is not a boolean
    * @return Property (Chainable)
    */
    public function set_required($required)
    {
        if(!is_bool($required)) {
            throw new \InvalidArgumentException('required must be a boolean');
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
    * @param boolean
    * @throws \InvalidArgumentException if the paramter is not a boolean
    * @return Property (Chainable)
    */
    public function set_unique($unique)
    {
        if(!is_bool($unique)) {
            throw new \InvalidArgumentException('unique must be a boolean');
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
    * @param boolean
    * @throws \InvalidArgumentException if the paramter is not a boolean
    * @return Property (Chainable)
    */
    public function set_active($active)
    {
        if(!is_bool($active)) {
            throw new \InvalidArgumentException('active must be a boolean');
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

    public function validator()
    {
        if($this->_validator === null) {
            $this->_validator = new PropertyValidator($this);
        }
        return $this->_validator;
    }

    public function validate(ValidatorInterface &$v=null)
    {
        if($v === null) {
            $v = $this->validator();
        }

        $ret = true;
        $ret = parent::validate($v) && $ret;
        $ret = $this->validate_required() && $ret;
        $ret = $this->validate_unique() && $ret;

        return $ret;
    }

    public function validate_required()
    {
        return true;
    }

    public function validate_unique()
    {
        return true;
    }

    /**
    *
    */
    public function fields()
    {
        $fields = [];
        if($this->l10n()) {
            $langs = ['fr', 'en'];
            foreach($langs as $lang) {
                $field = new Field();
                $field->set_data(
                    [
                    'ident'=>$this->ident().'_'.$lang,
                    'sql_type'=>$this->sql_type(),
                    'val'=>$this->field_val($lang),
                    'default_val'=>null,
                    'allow_null'=>true,
                    'comment'=>$this->label()
                    ]
                );
                $fields[$lang] = $field;
            }
        }
        else {
            $field = new Field();
            $field->set_data(
                [
                'ident'=>$this->ident(),
                'sql_type'=>$this->sql_type(),
                'val'=>$this->val(),
                'default_val'=>null,
                'allow_null'=>true,
                'comment'=>$this->label()
                ]
            );
            $fields[] = $field;
        }
        
        return $fields;
    }

    public function sql_type()
    {
        if($this->multiple()) {
            return 'TEXT';
        }
        else {
            return 'VARCHAR(255)';
        }
    }
}
