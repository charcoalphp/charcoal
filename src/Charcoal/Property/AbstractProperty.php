<?php

namespace Charcoal\Property;

use PDO;
use Exception;
use LogicException;
use RuntimeException;
use InvalidArgumentException;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

// From Pimple
use Pimple\Container;

// From 'charcoal-config'
use Charcoal\Config\AbstractEntity;

// From 'charcoal-core'
use Charcoal\Model\DescribableInterface;
use Charcoal\Model\DescribableTrait;
use Charcoal\Validator\ValidatableInterface;
use Charcoal\Validator\ValidatableTrait;
use Charcoal\Validator\ValidatorInterface;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;
use Charcoal\Translator\TranslatorAwareTrait;

// From 'charcoal-property'
use Charcoal\Property\DescribablePropertyInterface;
use Charcoal\Property\DescribablePropertyTrait;
use Charcoal\Property\PropertyInterface;
use Charcoal\Property\PropertyValidator;
use Charcoal\Property\StorablePropertyInterface;
use Charcoal\Property\StorablePropertyTrait;

/**
 * An abstract class that implements the full `PropertyInterface`.
 */
abstract class AbstractProperty extends AbstractEntity implements
    PropertyInterface,
    DescribableInterface,
    DescribablePropertyInterface,
    LoggerAwareInterface,
    StorablePropertyInterface,
    ValidatableInterface
{
    use LoggerAwareTrait;
    use DescribableTrait;
    use DescribablePropertyTrait;
    use StorablePropertyTrait;
    use TranslatorAwareTrait;
    use ValidatableTrait;

    const DEFAULT_L10N = false;
    const DEFAULT_MULTIPLE = false;
    const DEFAULT_HIDDEN = false;
    const DEFAULT_UNIQUE = false;
    const DEFAULT_REQUIRED = false;
    const DEFAULT_ALLOW_NULL = true;
    const DEFAULT_STORABLE = true;
    const DEFAULT_ACTIVE = true;

    /**
     * @var string
     */
    private $ident = '';

    /**
     * @var mixed
     */
    protected $val;

    /**
     * @var Translation|null
     */
    private $label;

    /**
     * @var boolean
     */
    private $l10n = self::DEFAULT_L10N;

    /**
     * @var boolean
     */
    private $multiple = self::DEFAULT_MULTIPLE;

    /**
     * Array of options for multiple properties
     * - `separator` (default=",") How the values will be separated in the storage (sql).
     * - `min` (default=null) The min number of values. If null, <0 or NaN, then this is not taken into consideration.
     * - `max` (default=null) The max number of values. If null, <0 or NaN, then there is not limit.
     * @var array|null
     */
    private $multipleOptions;

    /**
     * @var boolean
     */
    private $hidden = self::DEFAULT_HIDDEN;

    /**
     * If true, this property *must* have a value
     * @var boolean
     */
    private $required = self::DEFAULT_REQUIRED;

    /**
     * Unique properties should not share he same value across 2 objects
     * @var boolean
     */
    private $unique = self::DEFAULT_UNIQUE;

    /**
     * @var boolean $allowNull
     */
    private $allowNull = self::DEFAULT_ALLOW_NULL;

    /**
     * Only the storable properties should be saved in storage.
     * @var boolean
     */
    private $storable = self::DEFAULT_STORABLE;

    /**
     * Inactive properties should be hidden everywhere / unused
     * @var boolean
     */
    private $active = self::DEFAULT_ACTIVE;

    /**
     * @var Translation|null
     */
    private $description;

    /**
     * @var Translation|null
     */
    private $notes;

    /**
     * @var array|null
     */
    protected $viewOptions;

    /**
     * @var string
     */
    protected $displayType;

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * Required dependencies:
     * - `logger` a PSR3-compliant logger.
     * - `pdo` a PDO database.
     * - `translator` a Charcoal Translator (based on Symfony's).
     *
     * @param array $data Optional. Class Dependencies.
     */
    public function __construct(array $data = null)
    {
        $this->setLogger($data['logger']);
        $this->setPdo($data['database']);
        $this->setTranslator($data['translator']);

        // Optional DescribableInterface dependencies
        if (isset($data['property_factory'])) {
            $this->setPropertyFactory($data['property_factory']);
        }

        if (isset($data['metadata_loader'])) {
            $this->setMetadataLoader($data['metadata_loader']);
        }

        // DI Container can optionally be set in property constructor.
        if (isset($data['container'])) {
            $this->setDependencies($data['container']);
        }
    }

    /**
     * @return string
     * @deprecated
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
     * Set the property's identifier.
     *
     * @param  string $ident The property identifier.
     * @throws InvalidArgumentException  If the identifier is not a string.
     * @return self
     */
    public function setIdent($ident)
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
     * Retrieve the property's identifier.
     *
     * @return string
     */
    public function getIdent()
    {
        return $this->ident;
    }

    public function ident()
    {
        return $this->getIdent();
    }

    /**
     * Retrieve the property's localized identifier.
     *
     * @param  string|null $lang The language code to return the identifier with.
     * @throws LogicException If the property is not multilingual.
     * @throws RuntimeException If the property has no identifier.
     * @throws InvalidArgumentException If the language code is invalid.
     * @return string
     */
    public function l10nIdent($lang = null)
    {
        if ($this->ident === '') {
            throw new RuntimeException('Missing Property Identifier');
        }

        if (!$this['l10n']) {
            throw new LogicException(sprintf(
                'Property "%s" is not multilingual',
                $this->ident
            ));
        }

        if ($lang === null) {
            $lang = $this->translator()->getLocale();
        } elseif (!is_string($lang)) {
            throw new InvalidArgumentException(sprintf(
                'Language must be a string for Property "%s"',
                $this->ident
            ));
        }

        return sprintf('%1$s_%2$s', $this->ident, $lang);
    }

    /**
     * Set the property's value.
     *
     * @param  mixed $val The property (raw) value.
     * @return self
     * @deprecated
     */
    final public function setVal($val)
    {
        $this->val = $this->parseVal($val);

        return $this;
    }

    /**
     * Retrieve the property's value.
     *
     * @return mixed
     * @deprecated
     */
    final public function val()
    {
        return $this->val;
    }

    /**
     * Parse the given value.
     *
     * > Note: the base method (defined here) returns the current value intact.
     * > Other properties can reimplement this method to parse their values,
     * > such as {@see \Charcoal\Property\ObjectProperty::parseVal()} who could parse objects into object IDs.
     *
     * @param  mixed $val The value to be parsed (normalized).
     * @throws InvalidArgumentException If the value does not match property settings.
     * @return mixed Returns the parsed value.
     */
    final public function parseVal($val)
    {
        if ($this['allowNull']) {
            if ($val === null) {
                return null;
            }
        } elseif ($val === null) {
            throw new InvalidArgumentException(sprintf(
                'Property "%s" value can not be NULL (not allowed)',
                $this->ident()
            ));
        }

        if ($this['multiple']) {
            if (is_string($val)) {
                $val = explode($this->multipleSeparator(), $val);
            } else {
                $val = (array)$val;
            }

            if (!is_array($val)) {
                throw new InvalidArgumentException(sprintf(
                    'Value is multiple. It must be an array or a delimited string, received "%s"',
                    is_object($val) ? get_class($val) : gettype($val)
                ));
            }

            if (empty($val) === true) {
                if ($this['allowNull'] === false) {
                    throw new InvalidArgumentException(sprintf(
                        'Property "%s" value can not be NULL or empty (not allowed)',
                        $this->ident()
                    ));
                } else {
                    return [];
                }
            }
            $val = array_map([ $this, 'parseOne' ], $val);
        } else {
            if ($this['l10n']) {
                $val = $this->translator()->translation($val);
                if ($val) {
                    $val->sanitize([$this, 'parseOne']);
                }
            } else {
                $val = $this->parseOne($val);
            }
        }

        return $val;
    }

    /**
     * @param mixed $val A single value to parse.
     * @return mixed The parsed value.
     */
    public function parseOne($val)
    {
        return $val;
    }

    /**
     * @param   mixed $val     Optional. The value to to convert for input.
     * @param   array $options Optional input options.
     * @return  string
     */
    public function inputVal($val, array $options = [])
    {
        if ($val === null) {
            return '';
        }

        if (is_string($val)) {
            return $val;
        }

        /** Parse multilingual values */
        if ($this['l10n']) {
            $propertyValue = $this->l10nVal($val, $options);
            if ($propertyValue === null) {
                return '';
            }
        } elseif ($val instanceof Translation) {
            $propertyValue = (string)$val;
        } else {
            $propertyValue = $val;
        }

        /** Parse multiple values / ensure they are of array type. */
        if ($this['multiple']) {
            if (is_array($propertyValue)) {
                $propertyValue = implode($this->multipleSeparator(), $propertyValue);
            }
        }

        if (!is_scalar($propertyValue)) {
            $propertyValue = json_encode($propertyValue, JSON_PRETTY_PRINT);
        }

        return (string)$propertyValue;
    }

    /**
     * @param  mixed $val     The value to to convert for display.
     * @param  array $options Optional display options.
     * @return string
     */
    public function displayVal($val, array $options = [])
    {
        if ($val === null || $val === '') {
            return '';
        }

        /** Parse multilingual values */
        if ($this['l10n']) {
            $propertyValue = $this->l10nVal($val, $options);
            if ($propertyValue === null) {
                return '';
            }
        } elseif ($val instanceof Translation) {
            $propertyValue = (string)$val;
        } else {
            $propertyValue = $val;
        }

        $separator = $this->multipleSeparator();

        /** Parse multiple values / ensure they are of array type. */
        if ($this['multiple']) {
            if (!is_array($propertyValue)) {
                $propertyValue = explode($separator, $propertyValue);
            }
        }

        if ($separator === ',') {
            $separator = ', ';
        }

        if (is_array($propertyValue)) {
            $propertyValue = implode($separator, $propertyValue);
        }

        return (string)$propertyValue;
    }



    /**
     * @param mixed $label The property label.
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $this->translator()->translation($label);

        return $this;
    }

    /**
     * @return Translation
     */
    public function getLabel()
    {
        if ($this->label === null) {
            return ucwords(str_replace([ '.', '_' ], ' ', $this->ident()));
        }

        return $this->label;
    }

    /**
     * @param boolean $l10n The l10n, or "translatable" flag.
     * @return self
     */
    public function setL10n($l10n)
    {
        $this->l10n = !!$l10n;

        return $this;
    }

    /**
     * The l10n flag sets the property as being translatable, meaning the data is held for multple languages.
     *
     * @return boolean
     */
    public function getL10n()
    {
        return $this->l10n;
    }

    /**
     * @param boolean $hidden The hidden flag.
     * @return self
     */
    public function setHidden($hidden)
    {
        $this->hidden = !!$hidden;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set whether this property accepts multiple values or a single value.
     *
     * @param  boolean $multiple The multiple flag.
     * @return self
     */
    public function setMultiple($multiple)
    {
        if (!is_bool($multiple)) {
            if (is_array($multiple)) {
                $this->setMultipleOptions($multiple);
            } elseif (is_int($multiple)) {
                $this->setMultipleOptions([
                    'min' => $multiple,
                    'max' => $multiple
                ]);
            }
        }

        $this->multiple = !!$multiple;

        return $this;
    }

    /**
     * Determine if this property accepts multiple values or a single value.
     *
     * The multiple flag sets the property as being "repeatable", or allow to represent an array of multiple values.
     *
     * ## Notes
     * - The multiple flag can be forced to false (or true) in implementing property class.
     * - How a multiple behaves also depend on `multipleOptions`.
     *
     * @return boolean
     */
    public function getMultiple()
    {
        return $this->multiple;
    }

    /**
     * Set the multiple options / configuration, when property is `multiple`.
     *
     * ## Options structure
     * - `separator` (string) The separator charactor.
     * - `min` (integer) The minimum number of values. (0 = no limit).
     * - `max` (integer) The maximum number of values. (0 = no limit).
     *
     * @param array $multipleOptions The property multiple options.
     * @return self
     */
    public function setMultipleOptions(array $multipleOptions)
    {
        // The options are always merged with the defaults, to ensure minimum required array structure.
        $options = array_merge($this->defaultMultipleOptions(), $multipleOptions);
        $this->multipleOptions = $options;

        return $this;
    }

    /**
     * The options defining the property behavior when the multiple flag is set to true.
     *
     * @see    self::defaultMultipleOptions
     * @param  string|null $key Optional setting to retrieve from the options.
     * @return array|mixed|null
     */
    public function getMultipleOptions($key = null)
    {
        if ($this->multipleOptions === null) {
            $this->multipleOptions = $this->defaultMultipleOptions();
        }

        if (is_string($key)) {
            if (isset($this->multipleOptions[$key])) {
                return $this->multipleOptions[$key];
            } else {
                return null;
            }
        }

        return $this->multipleOptions;
    }

    /**
     * Output the property multiple options as json.
     *
     * @return string
     */
    public function multipleOptionsAsJson()
    {
        return json_encode($this->getMultipleOptions());
    }

    /**
     * Retrieve the default settings for a multi-value property.
     *
     * @return array
     */
    public function defaultMultipleOptions()
    {
        return [
            'separator' => ',',
            'min'       => 0,
            'max'       => 0
        ];
    }

    /**
     * Retrieve the value delimiter for a multi-value property.
     *
     * @return string
     */
    public function multipleSeparator()
    {
        return $this->getMultipleOptions('separator');
    }

    /**
     * @param boolean $allow The property allow null flag.
     * @return self
     */
    public function setAllowNull($allow)
    {
        $this->allowNull = !!$allow;

        return $this;
    }

    /**
     * The allow null flag sets the property as being able to be of a "null" value.
     *
     * ## Notes
     * - This flag typically modifies the storage database to also allow null values.
     *
     * @return boolean
     */
    public function getAllowNull()
    {
        return $this->allowNull;
    }

    /**
     * @param boolean $required The property required flag.
     * @return self
     */
    public function setRequired($required)
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
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param boolean $unique The property unique flag.
     * @return self
     */
    public function setUnique($unique)
    {
        $this->unique = !!$unique;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getUnique()
    {
        return $this->unique;
    }

    /**
     * @param boolean $active The property active flag. Inactive properties should have no effects.
     * @return self
     */
    public function setActive($active)
    {
        $this->active = !!$active;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $storable The storable flag.
     * @return self
     */
    public function setStorable($storable)
    {
        $this->storable = !!$storable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getStorable()
    {
        return $this->storable;
    }

    /**
     * @param mixed $description The property description.
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $this->translator()->translation($description);
        return $this;
    }

    /**
     * @return Translation|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $notes The property notes.
     * @return self
     */
    public function setNotes($notes)
    {
        $this->notes = $this->translator()->translation($notes);
        return $this;
    }

    /**
     * @return Translation|null
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * The property's default validation methods.
     *
     * - `required`
     * - `unique`
     * - `allowNull`
     *
     * ## Notes
     * - Those 3 base validation methods should always be merged, in implementing factory class.
     *
     * @return string[]
     */
    public function validationMethods()
    {
        return [
            'required',
            'unique',
            'allowNull'
        ];
    }

    /**
     * @return boolean
     */
    public function validateRequired()
    {
        if ($this['required'] && !$this->val()) {
            $this->validator()->error('Value is required.', 'required');

            return false;
        }

        return true;
    }

    /**
     * @return boolean
     */
    public function validateUnique()
    {
        if (!$this['unique']) {
            return true;
        }

        /** @todo Check in the model's storage if the value already exists. */
        return true;
    }

    /**
     * @return boolean
     */
    public function validateAllowNull()
    {
        if (!$this['allowNull'] && $this->val() === null) {
            $this->validator()->error('Value can not be null.', 'allowNull');

            return false;
        }

        return true;
    }



    /**
     * @param mixed $val The value, at time of saving.
     * @return mixed
     */
    public function save($val)
    {
        // By default, nothing to do
        return $this->parseVal($val);
    }

    /**
     * @param string $type The display type.
     * @return self
     */
    public function setDisplayType($type)
    {
        $this->displayType = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayType()
    {
        if (!$this->displayType) {
            $meta = $this->metadata();

            // This default would be defined in type-property.json (@see charcoal-property/metadata)
            if (isset($meta['admin']) && isset($meta['admin']['display_type'])) {
                $default = $meta['admin']['display_type'];
            } else {
                $default = 'charcoal/admin/property/display/text';
            }
            $this->setDisplayType($default);
        }

        return $this->displayType;
    }

    /**
     * View options.
     * @param string $ident The display ident (ex: charcoal/admin/property/display/text).
     * @return array Should ALWAYS be an array.
     */
    final public function viewOptions($ident = null)
    {
        // No options defined
        if (!$this->viewOptions) {
            return [];
        }

        // No ident defined
        if (!$ident) {
            return $this->viewOptions;
        }

        // Invalid ident
        if (!isset($this->viewOptions[$ident])) {
            return [];
        }

        // Success!
        return $this->viewOptions[$ident];
    }

    /**
     * Set view options for both display and input
     *
     * @param array $viewOpts View options.
     * @return self
     */
    final public function setViewOptions(array $viewOpts = [])
    {
        $this->viewOptions = $viewOpts;

        return $this;
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        $this->setPropertyFactory($container['property/factory']);
        $this->setMetadataLoader($container['metadata/loader']);
    }

    /**
     * Attempt to get the multilingual value in the requested language.
     *
     * @param  mixed $val  The multilingual value to lookup.
     * @param  mixed $lang The language to return the value in.
     * @return string|null
     */
    protected function l10nVal($val, $lang = null)
    {
        if (!is_string($lang)) {
            if (is_array($lang) && isset($lang['lang'])) {
                $lang = $lang['lang'];
            } else {
                $lang = $this->translator()->getLocale();
            }
        }

        if (isset($val[$lang])) {
            return $val[$lang];
        } else {
            return null;
        }
    }

    /**
     * Create a new metadata object.
     *
     * @param  array $data Optional metadata to merge on the object.
     * @see DescribableTrait::createMetadata()
     * @return PropertyMetadata
     */
    protected function createMetadata(array $data = null)
    {
        $class = $this->metadataClass();
        return new $class($data);
    }

    /**
     * Retrieve the class name of the metadata object.
     *
     * @see DescribableTrait::metadataClass()
     * @return string
     */
    protected function metadataClass()
    {
        return PropertyMetadata::class;
    }

    /**
     * Create a Validator object
     *
     * @see ValidatableTrait::createValidator()
     * @return ValidatorInterface
     */
    protected function createValidator()
    {
        $validator = new PropertyValidator($this);

        return $validator;
    }

    /**
     * @param PDO $pdo The database connection (PDO) instance.
     * @return void
     */
    private function setPdo(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
}
