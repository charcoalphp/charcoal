<?php

namespace Charcoal\Property;

use \PDO;
use \ArrayAccess;
use \RuntimeException;
use \InvalidArgumentException;

// From 'charcoal-core'
use \Charcoal\Model\Model;

// From 'charcoal-property'
use \Charcoal\Property\AbstractProperty;

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
     * Store the property's structure.
     *
     * @var array
     */
    private $structureData = [];

    /**
     * The class name of the "structure" collection to use.
     *
     * Must be a fully-qualified PHP namespace and an implementation of {@see ArrayAccess}.
     *
     * @var string
     */
    private $structureModelClass = Model::class;

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
     * Set the property's structure.
     *
     * @param  array|ArrayAccess $data The property's structure (fields, data).
     * @throws InvalidArgumentException If the structure is invalid.
     * @return StructureProperty
     */
    public function setStructureData($data)
    {
        if (!is_array($data) && !($data instanceof ArrayAccess)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Structure [%s] must implement ArrayAccess.',
                    (is_object($data) ? get_class($data) : gettype($data))
                )
            );
        }

        $this->structureData = $data;

        return $this;
    }

    /**
     * Retrieve the property's structure.
     *
     * @return string
     */
    public function structureData()
    {
        return $this->structureData;
    }

    /**
     * Create a structure.
     *
     * @throws RuntimeException If the structure is invalid.
     * @return ArrayAccess
     */
    private function createStructureModel()
    {
        $structClass = $this->structureModelClass();
        $structure   = new $structClass;

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
     * Set the class name of the structure.
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
     * Retrieve the class name of the structure.
     *
     * @return string
     */
    private function structureModelClass()
    {
        return $this->structureModelClass;
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
                $struct->setMetadata($this->structureData());
                $struct->setData($entry);

                $entries[] = $struct;
            }

            return $entries;
        } else {
            $struct = $this->createStructureModel();
            $struct->setMetadata($this->structureData());
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
