<?php

namespace Charcoal\Property;

use \PDO;
use \ArrayAccess;
use \RuntimeException;
use \InvalidArgumentException;

// From Pimple
use \Pimple\Container;

// From 'charcoal-core'
use \Charcoal\Model\Model;
use \Charcoal\Model\MetadataInterface;

// From 'charcoal-factory'
use \Charcoal\Factory\FactoryInterface;

// From 'charcoal-translation'
use \Charcoal\Translator\Translation;

// From 'charcoal-property'
use \Charcoal\Property\AbstractProperty;
use \Charcoal\Property\Structure\StructureMetadata;

/**
 * Structure Data Property
 *
 * Allows for multiple complex entries to a property, which are stored
 * as a JSON string in the model's storage source. Typical use cases would be
 * {@see \Charcoal\Cms\Property\TemplateOptionsProperty template options},
 * {@see \Charcoal\Property\MapStructureProperty geolocation coordinates},
 * details for a log , or a list of addresses or people.
 *
 * The property's "structured_data" attribute allows one to build a virtual
 * model using much of the same specifications used for defining object models.
 *
 * ## Examples
 *
 * **Example #1 — Address**
 *
 * With the use of the {@see \Charcoal\Admin\Widget\FormGroup\StructureFormGroup Structure Form Group},
 * a form UI can be embedded in the object form widget.
 *
 * ```json
 * {
 *     "properties": {
 *         "street_address": {
 *             "type": "string",
 *             "input_type": "charcoal/admin/property/input/textarea",
 *             "label": "Street Address"
 *         },
 *         "locality": {
 *             "type": "string",
 *             "label": "Municipality"
 *         },
 *         "administrative_area": {
 *             "type": "string",
 *             "multiple": true,
 *             "label": "Administrative Division(s)"
 *         },
 *         "postal_code": {
 *             "type": "string",
 *             "label": "Postal Code"
 *         },
 *         "country": {
 *             "type": "string",
 *             "label": "Country"
 *         }
 *     },
 *     "admin": {
 *         "form_group": {
 *             "title": "Address",
 *             "show_header": false,
 *             "properties": [
 *                 "street_address",
 *                 "locality",
 *                 "postal_code",
 *                 "administrative_area",
 *                 "country"
 *             ],
 *             "layout": {
 *                 "structure": [
 *                     { "columns": [ 1 ] },
 *                     { "columns": [ 5, 1 ] },
 *                     { "columns": [ 1, 1 ] }
 *                 ]
 *             }
 *         }
 *     }
 * }
 *
 * ```
 */
class StructureProperty extends AbstractProperty
{
    /**
     * Track the state of loaded metadata for the structure.
     *
     * @var boolean
     */
    private $isStructureFinalized = false;

    /**
     * The metadata interfaces to use as the structure.
     *
     * These are paths (PSR-4) to import.
     *
     * @var array
     */
    private $structureInterfaces = [];

    /**
     * Store the property's structure.
     *
     * @var array|null
     */
    private $structureMetadata;

    /**
     * Store the property's "terminal" structure.
     *
     * This represents the value of "structure_metadata" key on a property definition.
     * This should always be merged last, after the interfaces are imported.
     *
     * @var array|null
     */
    private $terminalStructureMetadata;

    /**
     * The class name of the "structure" collection to use.
     *
     * Must be a fully-qualified PHP namespace and an implementation of {@see ArrayAccess}.
     *
     * @var string
     */
    private $structureModelClass = Model::class;

    /**
     * Store the factory instance.
     *
     * @var FactoryInterface
     */
    protected $modelFactory;

    /**
     * Return a new Structure Property object.
     *
     * @param array|ArrayAccess $data The property's dependencies.
     */
    public function __construct($data)
    {
        parent::__construct($data);

        if (isset($data['structure_model'])) {
            $this->setStructureModelClass($data['structure_model']);
        }
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);
    }


    /**
     * Set an object model factory.
     *
     * @param FactoryInterface $factory The model factory, to create objects.
     * @return self
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the object model factory.
     *
     * @throws RuntimeException If the model factory was not previously set.
     * @return FactoryInterface
     */
    public function modelFactory()
    {
        if (!isset($this->modelFactory)) {
            throw new RuntimeException(
                sprintf('Model Factory is not defined for "%s"', get_class($this))
            );
        }

        return $this->modelFactory;
    }
    /**
     * Retrieve the property's type identifier.
     *
     * @return string
     */
    public function type()
    {
        return 'structure';
    }

    /**
     * Ensure l10n can not be TRUE for structure property.
     *
     * @see    AbstractProperty::setL10n()
     * @todo   Add support for L10N;
     * @param  boolean $flag The l10n, or "translatable" flag.
     * @throws InvalidArgumentException If the L10N argument is TRUE (must be FALSE).
     * @return IdProperty Chainable
     */
    public function setL10n($flag)
    {
        $flag = !!$flag;

        if ($flag === true) {
            throw new InvalidArgumentException(
                'The structure property can not be translatable.'
            );
        }

        return $this;
    }

    /**
     * L10N is always FALSE for structure property.
     *
     * @see    AbstractProperty::l10n()
     * @return boolean
     */
    public function l10n()
    {
        return false;
    }

    /**
     * Retrieve the property's structure.
     *
     * @return string
     */
    public function structureMetadata()
    {
        if ($this->structureMetadata === null || $this->isStructureFinalized === false) {
            $this->structureMetadata = $this->loadStructureMetadata();
        }

        return $this->structureMetadata;
    }

    /**
     * Set the property's structure.
     *
     * @param  MetadataInterface|array|null $data The property's structure (fields, data).
     * @throws InvalidArgumentException If the structure is invalid.
     * @return StructureProperty
     */
    public function setStructureMetadata($data)
    {
        if ($data === null) {
            $this->structureMetadata = $data;
            $this->terminalStructureMetadata = $data;
        } elseif (is_array($data)) {
            $struct = $this->createStructureMetadata();
            $struct->merge($data);

            $this->structureMetadata = $struct;
            $this->terminalStructureMetadata = $data;
        } elseif ($data instanceof MetadataInterface) {
            $this->structureMetadata = $data;
            $this->terminalStructureMetadata = $data;
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'Structure [%s] is invalid (must be array or an instance of %s).',
                    (is_object($data) ? get_class($data) : gettype($data)),
                    StructureMetadata::class
                )
            );
        }

        $this->isStructureFinalized = false;

        return $this;
    }

    /**
     * Load the property's structure.
     *
     * @return MetadataInterface
     */
    protected function loadStructureMetadata()
    {
        $struct = $this->createStructureMetadata();

        if ($this->isStructureFinalized === false) {
            $this->isStructureFinalized = true;

            $loader = $this->metadataLoader();
            $paths  = $this->structureInterfaces();
            if (!empty($paths)) {
                $ident  = sprintf('property/structure/%s', $this->ident());
                $struct = $loader->load($ident, $struct, $paths);
            }
        }

        if ($this->terminalStructureMetadata) {
            $struct->merge($this->terminalStructureMetadata);
        }

        return $struct;
    }

    /**
     * Retrieve the metadata interfaces used by the property as a structure.
     *
     * @return array
     */
    public function structureInterfaces()
    {
        if (empty($this->structureInterfaces)) {
            return $this->structureInterfaces;
        }

        return array_keys($this->structureInterfaces);
    }

    /**
     * Set the given metadata interfaces for the property to use as a structure.
     *
     * @param  array $interfaces One or more metadata interfaces to use.
     * @return StructureProperty
     */
    public function setStructureInterfaces(array $interfaces)
    {
        $this->structureInterfaces = [];

        $this->addStructureInterfaces($interfaces);

        return $this;
    }

    /**
     * Add the given metadata interfaces for the property to use as a structure.
     *
     * @param  array $interfaces One or more metadata interfaces to use.
     * @return StructureProperty
     */
    public function addStructureInterfaces(array $interfaces)
    {
        foreach ($interfaces as $interface) {
            $this->addStructureInterface($interface);
        }

        return $this;
    }

    /**
     * Add the given metadata interfaces for the property to use as a structure.
     *
     * @param  string $interface A metadata interface to use.
     * @throws InvalidArgumentException If the interface is not a string.
     * @return StructureProperty
     */
    public function addStructureInterface($interface)
    {
        if (!is_string($interface)) {
            throw new InvalidArgumentException(
                'Structure interface must to be a string'
            );
        }

        $interface = $this->parseStructureInterface($interface);

        $this->structureInterfaces[$interface] = true;
        $this->isStructureFinalized = false;

        return $this;
    }

    /**
     * Parse a metadata identifier from given interface.
     *
     * Change `\` and `.` to `/` and force lowercase
     *
     * @param  string $interface A metadata interface to convert.
     * @return string
     */
    protected function parseStructureInterface($interface)
    {
        $ident = preg_replace('/([a-z])([A-Z])/', '$1-$2', $interface);
        $ident = strtolower(str_replace('\\', '/', $ident));

        return $ident;
    }

    /**
     * Create a metadata store for structures.
     *
     * Similar to {@see \Charcoal\Model\DescribableTrait::createMetadata()}.
     *
     * @return MetadataInterface
     */
    private function createStructureMetadata()
    {
        return new StructureMetadata();
    }

    /**
     * Create a data-model structure.
     *
     * @todo   Add support for simple ArrayAccess models.
     * @throws RuntimeException If the structure is invalid.
     * @return ArrayAccess
     */
    private function createStructureModel()
    {
        $structClass = $this->structureModelClass();
        $structure   = $this->modelFactory()->create($structClass);

        if (!$structure instanceof ArrayAccess) {
            throw new RuntimeException(
                sprintf(
                    'Structure [%s] must implement ArrayAccess.',
                    $structClass
                )
            );
        }

        return $structure;
    }

    /**
     * Set the class name of the data-model structure.
     *
     * @param  string $className The class name of the structure.
     * @throws InvalidArgumentException If the class name is not a string.
     * @return StructureProperty
     */
    private function setStructureModelClass($className)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException(
                'Structure class name must be a string.'
            );
        }

        $this->structureModelClass = $className;

        return $this;
    }

    /**
     * Retrieve the class name of the data-model structure.
     *
     * @return string
     */
    private function structureModelClass()
    {
        return $this->structureModelClass;
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

        return json_encode($propertyValue, JSON_PRETTY_PRINT);
    }

    /**
     * Convert the given value into a structure.
     *
     * @param  mixed $val     The value to "structurize".
     * @param  array $options Optional structure options.
     * @return ModelInterface|ModelInterface[]
     */
    public function structureVal($val, array $options = [])
    {
        /** @todo Find a use for this */
        unset($options);

        if ($val === null) {
            return null;
        }

        if ($this->multiple()) {
            $entries = [];
            foreach ($val as $entry) {
                $struct = $this->createStructureModel();
                $struct->setMetadata($this->structureMetadata());
                $struct->setData($entry);

                $entries[] = $struct;
            }

            return $entries;
        } else {
            $struct = $this->createStructureModel();
            $struct->setMetadata($this->structureMetadata());
            $struct->setData($val);

            return $struct;
        }
    }

    /**
     * Retrieve the structure as a plain array.
     *
     * @return array
     */
    public function toStructure()
    {
        return $this->structureVal($this->val());
    }

    /**
     * Retrieve the structure as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->val();
    }

    /**
     * Retrieve the structure of items as JSON.
     *
     * @param  integer $options Bitmask of flags.
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->val(), $options);
    }

    /**
     * AbstractProperty > setVal(). Ensure val is an array
     *
     * @param  string|array $val The value to set.
     * @throws InvalidArgumentException If the value is invalid.
     * @return array
     */
    public function parseOne($val)
    {
        if ($val === null || $val === '') {
            if ($this->allowNull()) {
                return null;
            } else {
                throw new InvalidArgumentException(
                    'Value can not be NULL (not allowed)'
                );
            }
        }

        if (!is_array($val)) {
            $val = json_decode($val, true);
        }

        return $val;
    }

    /**
     * Retrieve the property's extra SQL field settings.
     *
     * @return string
     */
    public function sqlExtra()
    {
        return '';
    }

    /**
     * Retrieve the property's SQL data type (storage format).
     *
     * For a lack of better array support in mysql, data is stored as encoded JSON in a LONGTEXT.
     *
     * @return string
     */
    public function sqlType()
    {
        return 'LONGTEXT';
    }

    /**
     * Retrieve the property's PDO data type.
     *
     * @return integer
     */
    public function sqlPdoType()
    {
        return PDO::PARAM_STR;
    }
}
