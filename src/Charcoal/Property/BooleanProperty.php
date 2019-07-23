<?php

namespace Charcoal\Property;

use PDO;
use InvalidArgumentException;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-property'
use Charcoal\Property\AbstractProperty;

/**
 * Boolean Property
 */
class BooleanProperty extends AbstractProperty
{
    /**
     * The label for "true".
     *
     * @var Translation
     */
    private $trueLabel;

    /**
     * The label for "false".
     *
     * @var Translation
     */
    private $falseLabel;

    /**
     * @return string
     */
    public function type()
    {
        return 'boolean';
    }

    /**
     * @param mixed $val A single value to parse.
     * @see AbstractProperty::parseOne()
     * @see AbstractProperty::parseVal()
     * @return boolean
     */
    public function parseOne($val)
    {
        return !!$val;
    }

    /**
     * @param  mixed $val     The value to to convert for display.
     * @param  array $options Optional display options.
     * @see AbstractProperty::displayVal()
     * @return string
     */
    public function displayVal($val, array $options = [])
    {
        if ($val === true) {
            if (isset($options['true_label'])) {
                $label = $options['true_label'];
            } else {
                $label = $this['trueLabel'];
            }
        } else {
            if (isset($options['false_label'])) {
                $label = $options['false_label'];
            } else {
                $label = $this['falseLabel'];
            }
        }

        return $this->translator()->translate($label);
    }

    /**
     * Ensure multiple can never be true for boolean property.
     *
     * @param boolean $multiple The multiple flag.
     * @throws InvalidArgumentException If multiple is true. (must be false for boolean properties).
     * @see AbstractProperty::setMultiple()
     * @return self
     */
    public function setMultiple($multiple)
    {
        $multiple = !!$multiple;
        if ($multiple === true) {
            throw new InvalidArgumentException(
                'Multiple can not be true for boolean property.'
            );
        }
        return $this;
    }

    /**
     * Multiple is always false for boolean property.
     *
     * @see AbstractProperty::getMultiple()
     * @return boolean
     */
    public function getMultiple()
    {
        return false;
    }

    /**
     * @param mixed $label The true label.
     * @return self
     */
    public function setTrueLabel($label)
    {
        $this->trueLabel = $this->translator()->translation($label);
        return $this;
    }

    /**
     * @return Translation
     */
    public function getTrueLabel()
    {
        if ($this->trueLabel === null) {
            // Default value
            $this->setTrueLabel('True');
        }
        return $this->trueLabel;
    }

    /**
     * @param mixed $label The false label.
     * @return self
     */
    public function setFalseLabel($label)
    {
        $this->falseLabel = $this->translator()->translation($label);
        return $this;
    }

    /**
     * @return Translation
     */
    public function getFalseLabel()
    {
        if ($this->falseLabel === null) {
            // Default value
            $this->setFalseLabel('False');
        }
        return $this->falseLabel;
    }

    /**
     * Get the SQL type (Storage format).
     *
     * Boolean properties are stored as `TINYINT(1) UNSIGNED`
     *
     * @see StorablePropertyTrait::sqlType()
     * @return string The SQL type
     */
    public function sqlType()
    {
        $dbDriver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($dbDriver === 'sqlite') {
            return 'INT';
        } else {
            return 'TINYINT(1) UNSIGNED';
        }
    }

    /**
     * @see StorablePropertyTrait::sqlPdoType()
     * @return integer
     */
    public function sqlPdoType()
    {
        return PDO::PARAM_BOOL;
    }

    /**
     * @see SelectablePropertyTrait::choices()
     * @return array
     */
    public function choices()
    {
        $val = $this->val();
        return [
            [
                'label'    => $this['trueLabel'],
                'selected' => !!$val,
                'value'    => 1
            ],
            [
                'label'    => $this['falseLabel'],
                'selected' => !$val,
                'value'    => 0
            ]
        ];
    }
}
