<?php

namespace Charcoal\Property;

use PDO;
use ArrayAccess;
use RuntimeException;
use InvalidArgumentException;
use UnexpectedValueException;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\DescribableInterface;
use Charcoal\Model\MetadataInterface;
use Charcoal\Model\ModelInterface;
use Charcoal\Model\Model;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-property'
use Charcoal\Property\StructureProperty;
use Charcoal\Property\Structure\StructureMetadata;
use Charcoal\Property\Structure\StructureModel;

/**
 * Model Structure Data Property
 *
 * Allows for multiple complex entries to a property, which are stored
 * as a JSON string in the model's storage source. Typical use cases would be
 * {@see \Charcoal\Cms\Property\TemplateOptionsProperty template options},
 * {@see \Charcoal\Property\MapStructureProperty geolocation coordinates},
 * details for a log, or a list of addresses or people.
 *
 * The property's "structured_metadata" attribute allows one to build a virtual
 * model using much of the same specifications used for defining object models.
 * This allows you to constrain the kind of structure you need to store.
 * For any values that can't be bound to a model-like structure, consider using
 * {@see StructureProperty}.
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
 * ```
 */
class ModelStructureProperty extends StructureProperty
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
     * @var MetadataInterface|array|null
     */
    private $structureMetadata;

    /**
     * Store the property's "terminal" structure.
     *
     * This represents the value of "structure_metadata" key on a property definition.
     * This should always be merged last, after the interfaces are imported.
     *
     * @var MetadataInterface|array|null
     */
    private $terminalStructureMetadata;

    /**
     * Store the property's model prototype.
     *
     * @var ArrayAccess|DescribableInterface|null
     */
    private $structurePrototype;

    /**
     * The class name of the "structure" collection to use.
     *
     * Must be a fully-qualified PHP namespace and an implementation of {@see ArrayAccess}.
     *
     * @var string
     */
    private $structureModelClass = StructureModel::class;

    /**
     * Store the factory instance.
     *
     * @var FactoryInterface
     */
    protected $structureModelFactory;

    /**
     * Retrieve the property's type identifier.
     *
     * @return string
     */
    public function type()
    {
        return 'model-structure';
    }

    /**
     * Retrieve the property's structure.
     *
     * @return MetadataInterface|null
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
     * @return self
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
            throw new InvalidArgumentException(sprintf(
                'Structure [%s] is invalid (must be array or an instance of %s).',
                (is_object($data) ? get_class($data) : gettype($data)),
                StructureMetadata::class
            ));
        }

        $this->isStructureFinalized = false;

        return $this;
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
     * @return self
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
     * @return self
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
     * @return self
     */
    public function addStructureInterface($interface)
    {
        if (!is_string($interface)) {
            throw new InvalidArgumentException(sprintf(
                'Structure interface must to be a string, received %s',
                is_object($interface) ? get_class($interface) : gettype($interface)
            ));
        }

        if (!empty($interface)) {
            $interface = $this->parseStructureInterface($interface);

            $this->structureInterfaces[$interface] = true;
            $this->isStructureFinalized = false;
        }

        return $this;
    }

    /**
     * Load the property's structure.
     *
     * @return MetadataInterface
     */
    protected function loadStructureMetadata()
    {
        $structureMetadata = null;

        if ($this->isStructureFinalized === false) {
            $this->isStructureFinalized = true;

            $structureInterfaces = $this->structureInterfaces();
            if (!empty($structureInterfaces)) {
                $metadataLoader = $this->metadataLoader();
                $metadataClass  = $this->structureMetadataClass();

                $structureKey = $structureInterfaces;
                array_unshift($structureKey, $this->ident());
                $structureKey = 'property/structure='.$metadataLoader->serializeMetaKey($structureKey);

                $structureMetadata = $metadataLoader->load(
                    $structureKey,
                    $metadataClass,
                    $structureInterfaces
                );
            }
        }

        if ($structureMetadata === null) {
            $structureMetadata = $this->createStructureMetadata();
        }

        if ($this->terminalStructureMetadata) {
            $structureMetadata->merge($this->terminalStructureMetadata);
        }

        return $structureMetadata;
    }


    /**
     * Retrieve a singleton of the structure model for prototyping.
     *
     * @return ArrayAccess|DescribableInterface
     */
    public function structureProto()
    {
        if ($this->structurePrototype === null) {
            $model = $this->createStructureModel();

            if ($model instanceof DescribableInterface) {
                $model->setMetadata($this->structureMetadata());
            }

            $this->structurePrototype = $model;
        }

        return $this->structurePrototype;
    }

    /**
     * Set the class name of the data-model structure.
     *
     * @param  string $className The class name of the structure.
     * @throws InvalidArgumentException If the class name is not a string.
     * @return self
     */
    protected function setStructureModelClass($className)
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
    public function structureModelClass()
    {
        return $this->structureModelClass;
    }

    /**
     * Convert the given value into a structure.
     *
     * Options:
     * - `default_data` (_boolean_|_array_) — If TRUE, the default data defined
     *   in the structure's metadata is merged. If an array, that is merged.
     *
     * @param  mixed                   $val     The value to "structurize".
     * @param  array|MetadataInterface $options Optional structure options.
     * @throws InvalidArgumentException If the options are invalid.
     * @return ModelInterface|ModelInterface[]
     */
    public function structureVal($val, $options = [])
    {
        if ($val === null) {
            return ($this['multiple'] ? [] : null);
        }

        $metadata = clone $this->structureMetadata();

        if ($options instanceof MetadataInterface) {
            $metadata->merge($options);
        } elseif ($options === null) {
            $options = [];
        } elseif (is_array($options)) {
            if (isset($options['metadata'])) {
                $metadata->merge($options['metadata']);
            }
        } else {
            throw new InvalidArgumentException(sprintf(
                'Structure value options must to be an array or an instance of %2$s, received %1$s',
                is_object($options) ? get_class($options) : gettype($options),
                StructureMetadata::class
            ));
        }

        $defaultData = [];
        if (isset($options['default_data'])) {
            if (is_bool($options['default_data'])) {
                $withDefaultData = $options['default_data'];
                if ($withDefaultData) {
                    $defaultData = $metadata->defaultData();
                }
            } elseif (is_array($options['default_data'])) {
                $withDefaultData = true;
                $defaultData     = $options['default_data'];
            }
        }

        $val = $this->parseVal($val);

        if ($this['multiple']) {
            $entries = [];
            foreach ($val as $v) {
                $entries[] = $this->createStructureModelWith($metadata, $defaultData, $v);
            }

            return $entries;
        } else {
            return $this->createStructureModelWith($metadata, $defaultData, $val);
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
     * @param null|string $model Model ident.
     * @return ArrayAccess|DescribableInterface|mixed
     * @throws UnexpectedValueException If the structure is invalid.
     */
    public function toModel($model = null)
    {
        if ($model) {
            $structure = $this->structureModelFactory()->create($model);

            if (!$structure instanceof ArrayAccess) {
                throw new UnexpectedValueException(sprintf(
                    'Structure [%s] must implement [%s]',
                    $model,
                    ArrayAccess::class
                ));
            }

            return $structure;
        }

        return $this->structureProto();
    }

    /**
     * PropertyInterface::save().
     * @param  mixed $val The value, at time of saving.
     * @return mixed
     */
    public function save($val)
    {
        $val = parent::save($val);

        if ($this['multiple']) {
            $proto = $this->structureProto();
            if ($proto instanceof ModelInterface) {
                $objs = (array)$this->structureVal($val);
                $val  = [];
                if (!empty($objs)) {
                    $val  = [];
                    foreach ($objs as $obj) {
                        $obj->saveProperties();
                        $val[] = $obj->data();
                    }
                }
            }
        } else {
            $obj = $this->structureVal($val);
            if ($obj instanceof ModelInterface) {
                $obj->saveProperties();
                $val = $obj->data();
            }
        }

        return $val;
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setStructureModelFactory($container['model/factory']);
    }



    /**
     * Retrieve the structure model factory.
     *
     * @throws RuntimeException If the model factory was not previously set.
     * @return FactoryInterface
     */
    protected function structureModelFactory()
    {
        if (!isset($this->structureModelFactory)) {
            throw new RuntimeException(sprintf(
                'Model Factory is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->structureModelFactory;
    }


    /**
     * Set an structure model factory.
     *
     * @param FactoryInterface $factory The model factory, to create objects.
     * @return self
     */
    private function setStructureModelFactory(FactoryInterface $factory)
    {
        $this->structureModelFactory = $factory;

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
     * Create a new metadata object for structures.
     *
     * Similar to {@see \Charcoal\Model\DescribableTrait::createMetadata()}.
     *
     * @return MetadataInterface
     */
    protected function createStructureMetadata()
    {
        $class = $this->structureMetadataClass();
        return new $class();
    }

    /**
     * Retrieve the class name of the metadata object.
     *
     * @return string
     */
    protected function structureMetadataClass()
    {
        return StructureMetadata::class;
    }

    /**
     * Create a data-model structure.
     *
     * @todo   Add support for simple {@see ArrayAccess} models.
     * @throws UnexpectedValueException If the structure is invalid.
     * @return ArrayAccess
     */
    private function createStructureModel()
    {
        $structClass = $this->structureModelClass();
        $structure   = $this->structureModelFactory()->create($structClass);

        if (!$structure instanceof ArrayAccess) {
            throw new UnexpectedValueException(sprintf(
                'Structure [%s] must implement [%s]',
                $structClass,
                ArrayAccess::class
            ));
        }

        return $structure;
    }

    /**
     * Create a data-model structure.
     *
     * @param  MetadataInterface $metadata    The model's definition.
     * @param  array             ...$datasets The dataset(s) to modelize.
     * @throws UnexpectedValueException If the structure is invalid.
     * @return DescribableInterface
     */
    private function createStructureModelWith(
        MetadataInterface $metadata,
        array ...$datasets
    ) {
        $model = $this->createStructureModel();
        if (!$model instanceof DescribableInterface) {
            throw new UnexpectedValueException(sprintf(
                'Structure [%s] must implement [%s]',
                get_class($model),
                DescribableInterface::class
            ));
        }

        $model->setMetadata($metadata);

        if ($datasets) {
            foreach ($datasets as $data) {
                $model->setData($data);
            }
        }

        return $model;
    }
}
