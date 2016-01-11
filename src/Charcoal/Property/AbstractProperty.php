<?php

namespace Charcoal\Property;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;
use \JsonSerializable;
use \Serializable;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Model\DescribableInterface;
use \Charcoal\Model\DescribableTrait;
use \Charcoal\Translation\TranslationConfig;
use \Charcoal\Translation\TranslationString;
use \Charcoal\Validator\ValidatableInterface;
use \Charcoal\Validator\ValidatableTrait;
use \Charcoal\View\GenericView;
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

// Local namespace dependencies
use \Charcoal\Property\PropertyInterface;
use \Charcoal\Property\PropertyValidator;

/**
* An abstract class that implements the full `PropertyInterface`.
*/
abstract class AbstractProperty implements
    JsonSerializable,
    Serializable,
    PropertyInterface,
    DescribableInterface,
    LoggerAwareInterface,
    ValidatableInterface,
    ViewableInterface
{
    use LoggerAwareTrait;
    use DescribableTrait;
    use ValidatableTrait;
    use ViewableTrait;

    /**
    * @var string $ident
    */
    private $ident = '';

    /**
    * @var mixed $_val
    */
    protected $val;

    /**
    * @var TranslationString $label
    */
    private $label;

    /**
    * @var boolean $l10n
    */
    private $l10n = false;

    /**
    * @var boolean $hidden;
    */
    private $hidden = false;

    /**
    * @var boolean $multiple
    */
    private $multiple = false;

    /**
    * Array of options for multiple properties
    * - `separator` (default=",") How the values will be separated in the storage (sql)
    * - `min` (default=null) The minimum number of values. If null, <0 or NaN, then this is not taken into consideration
    * - `max` (default=null) The maximum number of values. If null, <0 or NaN, then there is not limit
    * @var mixed $multiple_options
    */
    private $multiple_options;

    /**
    * If true, this property *must* have a value
    * @var boolean $required
    */
    private $required = false;

    /**
    * Unique properties should not share he same value across 2 objects
    * @var boolean $unique
    */
    private $unique = false;

    /**
    * @var boolean $allow_null
    */
    private $allow_null = true;

    /**
    * Only the storable properties should be saved in storage.
    * @var boolean $storable
    */
    private $storable = true;

    /**
    * Inactive properties should be hidden everywhere / unused
    * @var boolean $active
    */
    private $active = true;

    /**
    * @var TranslationString $_description
    */
    private $description = '';

    /**
    * @var TranslationString $_notes
    */
    private $notes = '';

    /**
    * Required dependencies:
    * - `logger` a PSR3-compliant logger.
    *
    * @param array $data Optional. Class Dependencies.
    */
    public function __construct(array $data = null)
    {
        if (isset($data['logger'])) {
            $this->setLogger($data['logger']);
        }
    }

    /**
    *
    *
    * @return string
    */
    public function __toString()
    {
        $val = $this->val();
        if (is_string($val)) {
            return $val;
        } else {
            if (is_object($val)) {
                return (string)$val;
            } else {
                return '';
            }
        }
    }

    /**
    * Get the "property type" string.
    *
    * ## Notes
    * - Type can not be set, so it must be explicitely provided by each implementing property classes.
    *
    * @return string
    */
    abstract public function type();

    /**
    * This function takes an array and fill the property with its value.
    *
    * This method either calls a setter for each key (`set_{$key}()`) or sets a public member.
    *
    * For example, calling with `set_data(['ident'=>$ident])` would call `set_ident($ident)`
    * becasue `set_ident()` exists.
    *
    * But calling with `set_data(['foobar'=>$foo])` would set the `$foobar` member
    * on the metadata object, because the method `set_foobar()` does not exist.
    *
    * @param array $data
    * @return AbstractProperty Chainable
    */
    public function set_data(array $data)
    {
        foreach ($data as $prop => $val) {
            $setter = 'set_'.$prop;
            if (is_callable([$this, $setter])) {
                $this->{$setter}($val);
            } else {
                // Set as public member if setter is not set on object.
                $this->{$prop} = $val;
            }
        }

        return $this;
    }

    /**
    * @param string $ident
    * @throws InvalidArgumentException  If the ident parameter is not a string
    * @return AbstractProperty Chainable
    */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Ident needs to be string.'
            );
        }
        $this->ident = $ident;
        return $this;
    }

    /**
    * @throws Exception if trying to access getter before setter
    * @return string
    */
    public function ident()
    {
        if ($this->ident === null) {
            throw new Exception(
                'Can not get ident(): Ident was never set.'
            );
        }
        return $this->ident;
    }

    /**
    * @param mixed $val
    * @throws InvalidArgumentException
    * @return PropertyInterface Chainable
    */
    public function set_val($val)
    {
        if ($val === null) {
            if ($this->allow_null()) {
                $this->val = null;
                return $this;
            } else {
                throw new InvalidArgumentException(
                    'Val can not be null (Not allowed)'
                );
            }
        }
        if ($this->multiple()) {
            if (is_string($val)) {
                $val = explode($this->multiple_separator(), $val);
            }
            if (!is_array($val)) {
                throw new InvalidArgumentException(
                    'Val is multiple so it must be a string (convertable to array by separator) or an array'
                );
            }
        }
        $this->val = $val;
        return $this;
    }

    /**
    * @return mixed
    */
    public function val()
    {
        return $this->val;
    }

    /**
    * @param string $field_ident
    * @return mixed
    */
    public function field_val($field_ident)
    {
        $val = $this->val();

        if ($val === null) {
            return null;
        }
        if (is_scalar($val)) {
            return $this->storage_val($val);
        }
        if (isset($val[$field_ident])) {
            return $this->storage_val($val[$field_ident]);
        } else {
            return null;
        }
    }

    /**
    * Get the property's value in a format suitable for storage.
    *
    * @param mixed $val Optional.
    * @return mixed
    */
    public function storage_val($val = null)
    {
        if ($val === null) {
            $val = $this->val();
        }
        if ($val === null) {
            // Do not json_encode NULL values
            return null;
        }

        if ($this->multiple()) {
            if (is_array($val)) {
                $val = implode($this->multiple_separator(), $val);
            }
        }

        if (!is_scalar($val)) {
            return json_encode($val, true);
        }
        return $val;
    }

    /**
    * @param mixed $val Optional.
    * @return string
    */
    public function display_val($val = null)
    {
        if ($val === null) {
            $val = $this->val();
        }

        if ($val === null) {
            return '';
        }

        $property_value = $val;

        if ($this->l10n() === true) {
            $translator = TranslationConfig::instance();

            $property_value = $property_value[$translator->current_language()];
        }

        if ($this->multiple() === true) {
            if (is_array($property_value)) {
                $property_value = implode($this->multiple_separator(), $property_value);
            }
        }
        return (string)$property_value;
    }

    /**
    * @param mixed $label
    * @return PropertyInterface Chainable
    */
    public function set_label($label)
    {
        $this->label = new TranslationString($label);
        return $this;
    }

    /**
    * @return string
    */
    public function label()
    {
        if ($this->label === null) {
            return ucwords(str_replace(['.', '_'], ' ', $this->ident()));
        }
        return $this->label;
    }

    /**
    * @param boolean $l10n
    * @return PropertyInterface Chainable
    */
    public function set_l10n($l10n)
    {
        $this->l10n = !!$l10n;
        return $this;
    }

    /**
    * The l10n flag sets the property as being translatable, meaning the data is held for multple languages.
    *
    * @return boolean
    */
    public function l10n()
    {
        return $this->l10n;
    }

    /**
    * @param boolean $hidden
    * @return PropertyInterface Chainable
    */
    public function set_hidden($hidden)
    {
        $this->hidden = !!$hidden;
        return $this;
    }

    /**
    * @return boolean
    */
    public function hidden()
    {
        return $this->hidden;
    }

    /**
    * @param boolean $multiple
    * @return PropertyInterface Chainable
    */
    public function set_multiple($multiple)
    {
        $this->multiple = !!$multiple;
        return $this;
    }

    /**
    * The multiple flags sets the property as being "repeatable", or allow to represent an array of multiple values.
    *
    * ## Notes
    * - The multiple flag can be forced to false (or true) in implementing property class.
    * - How a multiple behaves also depend on `multiple_options`.
    *
    * @return boolean
    */
    public function multiple()
    {
        return $this->multiple;
    }

    /**
    * @param array $multiple_options
    * @return PropertyInterface Chainable
    */
    public function set_multiple_options(array $multiple_options)
    {
        // The options are always merged with the defaults, to ensure minimum required array structure.
        $options = array_merge($this->default_multiple_options(), $multiple_options);
        $this->multiple_options = $options;
        return $this;
    }

    /**
    * The options defining the property behavior when the multiple flag is set to true.
    *
    * ## Options structure
    * - `separator` (string) The separator charactor.
    * - `min` (integer) The minimum number of values. (0 = no limit).
    * - `max` (integer) The maximum number of values. (0 = no limit).
    *
    * @return array
    * @see self::default_multiple_options
    */
    public function multiple_options()
    {
        if ($this->multiple_options === null) {
            return $this->default_multiple_options();
        }
        return $this->multiple_options;
    }

    /**
    * @return array
    */
    public function default_multiple_options()
    {
        return [
            'separator' => ',',
            'min'       => 0,
            'max'       => 0
        ];
    }

    /**
    * @return string
    */
    public function multiple_separator()
    {
        $multiple_options = $this->multiple_options();
        return $multiple_options['separator'];
    }

    /**
    * @param boolean $allow
    * @return PropertyInterface Chainable
    */
    public function set_allow_null($allow)
    {
        $this->allow_null = !!$allow;
        return $this;
    }

    /**
    * The allow null flag sets the property as being able to be of a "null" value.
    *
    * ## Notes
    * - This flag typically modifies the storage database to also allow null values.
    * -
    *
    * @return boolean
    */
    public function allow_null()
    {
        return $this->allow_null;
    }

    /**
    * @param boolean $required
    * @return PropertyInterface Chainable
    */
    public function set_required($required)
    {
        $this->required = !!$required;
        return $this;
    }

    /**
    * Required flag sets the property as being required, meaning not allowed to be null / empty.
    *
    * ## Notes
    * - The actual meaning of "required" might be different for implementing property class.
    *
    * @return boolean
    */
    public function required()
    {
        return $this->required;
    }

    /**
    * @param boolean $unique
    * @return PropertyInterface Chainable
    */
    public function set_unique($unique)
    {
        $this->unique = !!$unique;
        return $this;
    }

    /**
    * @return boolean
    */
    public function unique()
    {
        return $this->unique;
    }

    /**
    * @param boolean $active
    * @return PropertyInterface Chainable
    */
    public function set_active($active)
    {
        $this->active = !!$active;
        return $this;
    }

    /**
    * @return boolean
    */
    public function active()
    {
        return $this->active;
    }

    /**
    * @param boolean $storable
    * @throws InvalidArgumentException If paramter is not a boolean.
    */
    public function set_storable($storable)
    {
        $this->storable = !!$storable;
        return $this;
    }

    /**
    * @return boolean
    */
    public function storable()
    {
        return $this->storable;
    }

    /**
    * @param mixed $description
    * @return PropertyInterface Chainable
    */
    public function set_description($description)
    {
        $this->description = new TranslationString($description);
        return $this;
    }

    /**
    * @return string
    */
    public function description()
    {
        return $this->description;
    }

    /**
    * @param mixed $notes
    * @return PropertyInterface Chainable
    */
    public function set_notes($notes)
    {
        $this->notes = new TranslationString($notes);
        return $this;
    }

    /**
    * @return string
    */
    public function notes()
    {
        return $this->notes;
    }

    /**
    * @return array
    */
    public function fields()
    {
        $fields = [];
        if ($this->l10n()) {
            $translator = TranslationConfig::instance();

            foreach ($translator->languages() as $lang_code) {
                $ident = sprintf('%1$s_%2$s', $this->ident(), $lang_code);
                $field = new PropertyField();
                $field->set_data(
                    [
                        'ident'        => $ident,
                        'sql_type'     => $this->sql_type(),
                        'sql_pdo_type' => $this->sql_pdo_type(),
                        'extra'        => $this->sql_extra(),
                        'val'          => $this->field_val($lang_code),
                        'default_val'  => null,
                        'allow_null'   => $this->allow_null(),
                        'comment'      => $this->label()
                    ]
                );
                $fields[$lang_code] = $field;
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
    * The property's default validation methods/
    *
    * - `required`
    * - `unique`
    * - `allow_null`
    *
    * ## Notes
    * - Those 3 base validation methods should always be merged, in implementing factory class.
    *
    * @return array
    */
    public function validation_methods()
    {
        return [
            'required',
            'unique',
            'allow_null'
        ];
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
    * @todo
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
        if (isset($this->{$property_ident})) {
            return $this->{$property_ident};
        } else {
            return null;
        }
    }

    /**
    * @param array $data Optional. Metadata data.
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
    * @param array $data Optional. View data.
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

    /**
    * Serializable > serialize()
    *
    * @return string
    */
    public function serialize()
    {
        $data = $this->val();
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
        $this->set_val($data);
    }

    /**
    * JsonSerializable > jsonSerialize()
    *
    * @return mixed
    */
    public function jsonSerialize()
    {
        return $this->val();
    }
}
