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

    /**
     * @var string $ident
     */
    private $ident = '';

    /**
     * @var mixed $Val
     */
    protected $val;

    /**
     * @var Translation|null $label
     */
    private $label;

    /**
     * @var boolean $l10n
     */
    private $l10n = false;

    /**
     * @var boolean $hidden ;
     */
    private $hidden = false;

    /**
     * @var boolean $multiple
     */
    private $multiple = false;

    /**
     * Array of options for multiple properties
     * - `separator` (default=",") How the values will be separated in the storage (sql).
     * - `min` (default=null) The min number of values. If null, <0 or NaN, then this is not taken into consideration.
     * - `max` (default=null) The max number of values. If null, <0 or NaN, then there is not limit.
     * @var mixed $multipleOptions
     */
    private $multipleOptions;

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
     * @var boolean $allowNull
     */
    private $allowNull = true;

    /**
     * An empty value implies that the property will inherit the table's encoding
     * @var string $sqlEncoding
     */
    private $sqlEncoding = '';

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
     * @var Translation|null $description
     */
    private $description;

    /**
     * @var Translation|null $notes
     */
    private $notes;

    /**
     * @var array $viewOptions
     */
    protected $viewOptions;

    /**
     * @var string $displayType
     */
    protected $displayType;

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * Required dependencies:
     * - `logger` a PSR3-compliant logger.
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
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        $this->setPropertyFactory($container['property/factory']);
        $this->setMetadataLoader($container['metadata/loader']);
    }

    /**
     * @param PDO $pdo The database connection (PDO) instance.
     * @return void
     */
    private function setPdo(PDO $pdo)
    {
        $this->pdo = $pdo;
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
     * Set the property's identifier.
     *
     * @param  string $ident The property identifier.
     * @throws InvalidArgumentException  If the identifier is not a string.
     * @return PropertyInterface Chainable
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
    public function ident()
    {
        return $this->ident;
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
        if (!$this->l10n()) {
            throw new LogicException('Property is not multilingual');
        }

        if ($this->ident === '') {
            throw new RuntimeException('Missing Property Identifier');
        }

        if ($lang === null) {
            $lang = $this->translator()->getLocale();
        } elseif (!is_string($lang)) {
            throw new InvalidArgumentException('Language must be a string');
        }

        return sprintf('%1$s_%2$s', $this->ident, $lang);
    }

    /**
     * Set the property's value.
     *
     * @param  mixed $val The property (raw) value.
     * @return PropertyInterface Chainable
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
        if ($this->allowNull()) {
            if ($val === null) {
                return null;
            }
        } elseif ($val === null) {
            throw new InvalidArgumentException(
                sprintf('Property "%s" value can not be NULL (not allowed)', $this->ident())
            );
        }

        if ($this->multiple()) {
            if (is_string($val)) {
                $val = explode($this->multipleSeparator(), $val);
            }

            if (!is_array($val)) {
                throw new InvalidArgumentException(
                    'Value is multiple. It must be a string (convertable to array by separator) or an array'
                );
            }

            $val = array_map([ $this, 'parseOne' ], $val);
        } else {
            $val = $this->parseOne($val);
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
        if ($this->l10n()) {
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
        if ($this->multiple()) {
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
        if ($this->l10n()) {
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
        if ($this->multiple()) {
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
     * @param mixed $label The property label.
     * @return PropertyInterface Chainable
     */
    public function setLabel($label)
    {
        $this->label = $this->translator()->translation($label);

        return $this;
    }

    /**
     * @return Translation
     */
    public function label()
    {
        if ($this->label === null) {
            return ucwords(str_replace([ '.', '_' ], ' ', $this->ident()));
        }

        return $this->label;
    }

    /**
     * @param boolean $l10n The l10n, or "translatable" flag.
     * @return PropertyInterface Chainable
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
    public function l10n()
    {
        return $this->l10n;
    }

    /**
     * @param boolean $hidden The hidden flag.
     * @return PropertyInterface Chainable
     */
    public function setHidden($hidden)
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
     * Set whether this property accepts multiple values or a single value.
     *
     * @param  boolean $multiple The multiple flag.
     * @return PropertyInterface Chainable
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
    public function multiple()
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
     * @return PropertyInterface Chainable
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
     * @return array
     */
    public function multipleOptions($key = null)
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
        return json_encode($this->multipleOptions());
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
        return $this->multipleOptions('separator');
    }

    /**
     * @param boolean $allow The property allow null flag.
     * @return PropertyInterface Chainable
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
    public function allowNull()
    {
        return $this->allowNull;
    }

    /**
     * Set the property's SQL encoding & collation.
     *
     * @param  string $ident The encoding ident.
     * @throws InvalidArgumentException  If the identifier is not a string.
     * @return PropertyInterface Chainable
     */
    public function setSqlEncoding($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Encoding ident needs to be string.'
            );
        }

        if ($ident === 'utf8mb4') {
            $this->sqlEncoding = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        }

        return $this;
    }

    /**
     * Retrieve the property's SQL encoding ident.
     *
     * @return string
     */
    public function sqlEncoding()
    {
        return $this->sqlEncoding;
    }

    /**
     * @param boolean $required The property required flag.
     * @return PropertyInterface Chainable
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
    public function required()
    {
        return $this->required;
    }

    /**
     * @param boolean $unique The property unique flag.
     * @return PropertyInterface Chainable
     */
    public function setUnique($unique)
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
     * @param boolean $active The property active flag. Inactive properties should have no effects.
     * @return PropertyInterface Chainable
     */
    public function setActive($active)
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
     * @param boolean $storable The storable flag.
     * @return PropertyInterface Chainable
     */
    public function setStorable($storable)
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
     * @param mixed $description The property description.
     * @return PropertyInterface Chainable
     */
    public function setDescription($description)
    {
        $this->description = $this->translator()->translation($description);
        return $this;
    }

    /**
     * @return Translation
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * @param mixed $notes The property notes.
     * @return PropertyInterface Chainable
     */
    public function setNotes($notes)
    {
        $this->notes = $this->translator()->translation($notes);
        return $this;
    }

    /**
     * @return Translation
     */
    public function notes()
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
        if ($this->required() && !$this->val()) {
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
        if (!$this->unique()) {
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
        if (!$this->allowNull() && $this->val() === null) {
            $this->validator()->error('Value can not be null.', 'allowNull');

            return false;
        }

        return true;
    }

    /**
     * @param array $data Optional. Metadata data.
     * @return PropertyMetadata
     */
    protected function createMetadata(array $data = null)
    {
        $metadata = new PropertyMetadata();
        if ($data !== null) {
            $metadata->setData($data);
        }

        return $metadata;
    }

    /**
     * ValidatableTrait > createValidator(). Create a Validator object
     *
     * @return ValidatorInterface
     */
    protected function createValidator()
    {
        $validator = new PropertyValidator($this);

        return $validator;
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
     * @return PropertyInterface Chainable
     */
    public function setDisplayType($type)
    {
        $this->displayType = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function displayType()
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
     * @return PropertyInterface Chainable
     */
    final public function setViewOptions(array $viewOpts = [])
    {
        $this->viewOptions = $viewOpts;

        return $this;
    }
}
