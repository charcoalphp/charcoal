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
use Charcoal\Translator\TranslatableInterface;
use Charcoal\Translator\TranslatableValue;
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
    ValidatableInterface,
    \Stringable
{
    use LoggerAwareTrait;
    use DescribableTrait;
    use DescribablePropertyTrait;
    use StorablePropertyTrait;
    use TranslatorAwareTrait;
    use ValidatableTrait;

    public const DEFAULT_L10N = false;
    public const DEFAULT_MULTIPLE = false;
    public const DEFAULT_HIDDEN = false;
    public const DEFAULT_UNIQUE = false;
    public const DEFAULT_REQUIRED = false;
    public const DEFAULT_ALLOW_NULL = true;
    public const DEFAULT_STORABLE = true;
    public const DEFAULT_VALIDATABLE = true;
    public const DEFAULT_ACTIVE = true;

    private string $ident = '';

    /**
     * @var mixed
     */
    protected $val;

    /**
     * @var Translation|null
     */
    private $label;

    private bool $l10n = self::DEFAULT_L10N;

    private bool $multiple = self::DEFAULT_MULTIPLE;

    /**
     * Array of options for multiple properties
     * - `separator` (default=",") How the values will be separated in the storage (sql).
     * - `min` (default=null) The min number of values. If null, <0 or NaN, then this is not taken into consideration.
     * - `max` (default=null) The max number of values. If null, <0 or NaN, then there is not limit.
     * @var array|null
     */
    private $multipleOptions;

    private bool $hidden = self::DEFAULT_HIDDEN;

    /**
     * If true, this property *must* have a value
     */
    private bool $required = self::DEFAULT_REQUIRED;

    /**
     * Unique properties should not share he same value across 2 objects
     */
    private bool $unique = self::DEFAULT_UNIQUE;

    private bool $allowNull = self::DEFAULT_ALLOW_NULL;

    /**
     * Only the storable properties should be saved in storage.
     */
    private bool $storable = self::DEFAULT_STORABLE;

    /**
     * Whether to validate the property.
     */
    private bool $validatable = self::DEFAULT_VALIDATABLE;

    /**
     * Inactive properties should be hidden everywhere / unused
     */
    private bool $active = self::DEFAULT_ACTIVE;

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
    public function __construct(?array $data = null)
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

    #[\Deprecated]
    public function __toString(): string
    {
        $val = $this->val();
        if (is_string($val)) {
            return $val;
        } elseif (is_object($val)) {
            return (string)$val;
        } else {
            return '';
        }
    }

    /**
     * Get the "property type" string.
     *
     * ## Notes
     * - Type can not be set, so it must be explicitely provided by each implementing property classes.
     */
    abstract public function type(): string;

    /**
     * Set the property's identifier.
     *
     * @param string $ident The property identifier.
     * @throws InvalidArgumentException  If the identifier is not a string.
     */
    public function setIdent(string $ident): static
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * Retrieve the property's identifier.
     *
     * @return string
     */
    public function getIdent(): string
    {
        return $this->ident;
    }

    /**
     * Legacy support of ident() instead of getIdent().
     *
     * @return string
     */
    public function ident()
    {
        return $this->getIdent();
    }

    /**
     * Retrieve the property's localized identifier.
     *
     * @param string|null $lang The language code to return the identifier with.
     * @throws RuntimeException If the property has no identifier.
     * @throws InvalidArgumentException If the language code is invalid.
     * @throws LogicException If the property is not multilingual.
     */
    public function l10nIdent(?string $lang = null): string
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
     * Retrieve the property's identifier formatted for field names.
     *
     * Overrides {@see StorablePropertyTrait::fieldIdent()} to satisfy
     * {@see StorablePropertyInterface} which requires a non-nullable string.
     *
     * @param string|null $key The field key to suffix to the property identifier.
     */
    public function fieldIdent(?string $key = null): string
    {
        if ($this->fieldIdent === null) {
            $this->fieldIdent = $this->snakeize($this['ident']);
        }

        if ($key === null || $key === '') {
            return $this->fieldIdent;
        }

        if ($this->isValidFieldKey($key)) {
            return $this->fieldIdent . '_' . $this->snakeize($key);
        }

        return '';
    }

    /**
     * Set the property's value.
     *
     * @deprecated
     *
     * @param  mixed $val The property (raw) value.
     * @return self
     */
    #![\Deprecated]
    final public function setVal($val)
    {
        $this->val = $this->parseVal($val);

        return $this;
    }

    /**
     * Clear the property's value.
     *
     * @deprecated
     *
     * @return self
     */
    #![\Deprecated]
    final public function clearVal()
    {
        $this->val = null;

        return $this;
    }

    /**
     * Retrieve the property's value.
     *
     * @deprecated
     *
     * @return mixed
     */
    #![\Deprecated]
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
     * @param mixed $val The value to be parsed (normalized).
     * @return mixed Returns the parsed value.
     * @throws InvalidArgumentException If the value does not match property settings.
     */
    final public function parseVal(mixed $val): mixed
    {
        if ($this['allowNull']) {
            if ($val === null || $val === '') {
                return null;
            }
        } elseif ($val === null) {
            throw new InvalidArgumentException(sprintf(
                'Property "%s" value can not be NULL (not allowed)',
                $this->ident()
            ));
        }

        if ($this['multiple']) {
            $val = $this->parseValAsMultiple($val);
            if (empty($val)) {
                if ($this['allowNull'] === false) {
                    throw new InvalidArgumentException(sprintf(
                        'Property "%s" value can not be NULL or empty (not allowed)',
                        $this->ident()
                    ));
                }

                return $val;
            }
            $val = array_map($this->parseOne(...), $val);
        } elseif ($this['l10n']) {
            $val = $this->parseValAsL10n($val);
            if ($val instanceof \Charcoal\Translator\TranslatableInterface) {
                $val->sanitize($this->parseOne(...));
            }
        } else {
            $val = $this->parseOne($val);
        }

        return $val;
    }

    /**
     * @param mixed $val A single value to parse.
     * @return mixed The parsed value.
     */
    public function parseOne(mixed $val): mixed
    {
        return $val;
    }

    /**
     * @param   mixed $val     Optional. The value to to convert for input.
     * @param   array $options Optional input options.
     */
    public function inputVal($val, array $options = []): string
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
        } elseif ($val instanceof TranslatableValue) {
            $propertyValue = $val->trans($this->translator());
        } elseif ($val instanceof Translation) {
            $propertyValue = (string)$val;
        } else {
            $propertyValue = $val;
        }

        /** Parse multiple values / ensure they are of array type. */
        if ($this['multiple'] && is_array($propertyValue)) {
            $propertyValue = implode($this->multipleSeparator(), $propertyValue);
        }

        if (!is_scalar($propertyValue)) {
            if (isset($options['json'])) {
                $flags = $options['json'];
            } elseif (($options['pretty'] ?? false)) {
                $flags = JSON_PRETTY_PRINT;
            }
            return json_encode($propertyValue, ($flags ?? 0));
        }

        return (string)$propertyValue;
    }

    /**
     * @param  mixed $val     The value to to convert for display.
     * @param  array $options Optional display options.
     */
    public function displayVal($val, array $options = []): string
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

        /** Parse multiple values / ensure they are of array type. */
        if ($this['multiple'] && !is_array($propertyValue)) {
            $propertyValue = $this->parseValAsMultiple($propertyValue);
        }

        if (is_array($propertyValue)) {
            $separator = $this->multipleSeparator();
            if ($separator === ',') {
                $separator = ', ';
            }

            $propertyValue = implode($separator, $propertyValue);
        }

        return (string)$propertyValue;
    }

    /**
     * @param mixed $label The property label.
     */
    public function setLabel(mixed $label): static
    {
        $this->label = $this->translator()->translation($label);

        return $this;
    }

    public function getLabel(): mixed
    {
        if ($this->label === null) {
            return ucwords(str_replace([ '.', '_' ], ' ', $this->ident()));
        }

        return $this->label;
    }

    /**
     * @param boolean $l10n The l10n, or "translatable" flag.
     */
    public function setL10n(bool $l10n): static
    {
        $this->l10n = (bool)$l10n;

        return $this;
    }

    /**
     * The l10n flag sets the property as being translatable, meaning the data is held for multple languages.
     */
    public function getL10n(): bool
    {
        return $this->l10n;
    }

    /**
     * @param  mixed $val A L10N variable.
     * @return TranslatableInterface|null The translation value.
     */
    public function parseValAsL10n($val): ?TranslatableInterface
    {
        return $this->translator()->translation($val);
    }

    /**
     * @param boolean $hidden The hidden flag.
     */
    public function setHidden(bool $hidden): static
    {
        $this->hidden = (bool)$hidden;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * Set whether this property accepts multiple values or a single value.
     *
     * @param boolean $multiple The multiple flag.
     */
    public function setMultiple(bool $multiple): static
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

        $this->multiple = $multiple;

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
    public function getMultiple(): bool
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
     */
    public function setMultipleOptions(array $multipleOptions): static
    {
        // The options are always merged with the defaults, to ensure minimum required array structure.
        $options = array_merge($this->defaultMultipleOptions(), $multipleOptions);
        $this->multipleOptions = $options;

        return $this;
    }

    /**
     * The options defining the property behavior when the multiple flag is set to true.
     *
     * @param  string|null $key Optional setting to retrieve from the options.
     * @see    self::defaultMultipleOptions
     */
    #[\Override ]
    public function getMultipleOptions(?string $key = null): mixed
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
    public function multipleSeparator(): string
    {
        return $this->getMultipleOptions('separator');
    }

    /**
     * @param  mixed $val A multi-value variable.
     * @return array The array of values.
     */
    public function parseValAsMultiple($val)
    {
        if (is_array($val)) {
            return $val;
        }

        if ($val === null || $val === '') {
            return [];
        }

        if (!is_string($val)) {
            return (array)$val;
        }

        return explode($this->multipleSeparator(), $val);
    }

    /**
     * @param boolean $allow The property allow null flag.
     */
    public function setAllowNull(bool $allow): static
    {
        $this->allowNull = (bool)$allow;

        return $this;
    }

    /**
     * The allow null flag sets the property as being able to be of a "null" value.
     *
     * ## Notes
     * - This flag typically modifies the storage database to also allow null values.
     */
    public function getAllowNull(): bool
    {
        return $this->allowNull;
    }

    /**
     * @param boolean $required The property required flag.
     */
    public function setRequired(bool $required): static
    {
        $this->required = (bool)$required;

        return $this;
    }

    /**
     * Required flag sets the property as being required, meaning not allowed to be null / empty.
     *
     * ## Notes
     * - The actual meaning of "required" might be different for implementing property class.
     */
    public function getRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param boolean $unique The property unique flag.
     */
    public function setUnique(bool $unique): static
    {
        $this->unique = (bool)$unique;

        return $this;
    }

    public function getUnique(): bool
    {
        return $this->unique;
    }

    /**
     * @param boolean $active The property active flag. Inactive properties should have no effects.
     */
    public function setActive(bool $active): static
    {
        $this->active = (bool)$active;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * Legacy support of active() instead of getActive().
     */
    public function active(): bool|string
    {
        return $this->getActive();
    }

    /**
     * @param  boolean $validatable The validatable flag.
     */
    public function setValidatable($validatable): static
    {
        $this->validatable = (bool)$validatable;

        return $this;
    }

    public function getValidatable(): bool
    {
        return $this->validatable;
    }

    /**
     * @param boolean $storable The storable flag.
     */
    public function setStorable(bool $storable): static
    {
        $this->storable = (bool)$storable;

        return $this;
    }

    public function getStorable(): bool
    {
        return $this->storable;
    }

    /**
     * @param mixed $description The property description.
     */
    public function setDescription($description): static
    {
        $this->description = $this->translator()->translation($description);
        return $this;
    }

    public function getDescription(): ?Translation
    {
        return $this->description;
    }

    /**
     * @param mixed $notes The property notes.
     */
    public function setNotes($notes): static
    {
        $this->notes = $this->translator()->translation($notes);
        return $this;
    }

    public function getNotes(): ?Translation
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
    public function validationMethods(): array
    {
        return [
            'required',
            'unique',
            'allowNull',
        ];
    }

    public function validateRequired(): bool
    {
        $val = $this->val();
        if ($this['required'] && empty($val) && !is_numeric($val)) {
            $this->validator()->error('Value is required.', 'required');

            return false;
        }

        return true;
    }

    public function validateUnique(): bool
    {
        /** @todo Check in the model's storage if the value already exists. */
        return true;
    }

    public function validateAllowNull(): bool
    {
        $val = $this->val();
        if (!$this['allowNull'] && $val === null) {
            $this->validator()->error('Value can not be null.', 'allowNull');

            return false;
        }

        return true;
    }

    /**
     * @param mixed $val The value, at time of saving.
     */
    public function save(mixed $val): mixed
    {
        // By default, nothing to do
        return $this->parseVal($val);
    }

    /**
     * @param string $type The display type.
     */
    public function setDisplayType($type): static
    {
        $this->displayType = $type;

        return $this;
    }

    public function getDisplayType(): string
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
    protected function l10nVal(mixed $val, mixed $lang = null): mixed
    {
        if (!is_string($lang)) {
            $lang = is_array($lang) && isset($lang['lang']) ? $lang['lang'] : $this->translator()->getLocale();
        }

        return ($val[$lang] ?? null);
    }

    /**
     * Create a new metadata object.
     *
     * @param  array $data Optional metadata to merge on the object.
     * @see DescribableTrait::createMetadata()
     * @return PropertyMetadata
     */
    protected function createMetadata(?array $data = null)
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
        return new PropertyValidator($this);
    }

    /**
     * @param PDO $pdo The database connection (PDO) instance.
     */
    private function setPdo(PDO $pdo): void
    {
        $this->pdo = $pdo;
    }
}
