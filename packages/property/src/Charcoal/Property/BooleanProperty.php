<?php

declare(strict_types=1);

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

    public function type(): string
    {
        return 'boolean';
    }

    /**
     * @param  mixed $val A single value to parse.
     *@see AbstractProperty::parseVal()
     *
     * @see AbstractProperty::parseOne()
     */
    #[\Override]
    public function parseOne(mixed $val): bool
    {
        return (bool)$val;
    }

    /**
     * @param  mixed $val     The value to to convert for display.
     * @see AbstractProperty::displayVal()
     *
     * @param  mixed $val     The value to to convert for display.
     * @param  array $options Optional display options.
     */
    #[\Override]
    public function displayVal($val, array $options = []): string
    {
        if ($val === true) {
            $label = ($options['true_label'] ?? $this['trueLabel']);
        } elseif (isset($options['false_label'])) {
            $label = $options['false_label'];
        } else {
            $label = $this['falseLabel'];
        }

        return $this->translator()->translate($label);
    }

    /**
     * Ensure multiple can never be true for boolean property.
     *
     * @param  boolean $multiple The multiple flag.
     * @throws InvalidArgumentException If multiple is true. (must be false for boolean properties).
     * @see AbstractProperty::setMultiple()
     *
     */
    #[\Override]
    public function setMultiple(bool $multiple): static
    {
        $multiple = (bool)$multiple;
        if ($multiple) {
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
     */
    #[\Override]
    public function getMultiple(): bool
    {
        return false;
    }

    /**
     * @param  mixed $label The true label.
     */
    public function setTrueLabel($label): static
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
     * @param  mixed $label The false label.
     */
    public function setFalseLabel($label): static
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
     *
     * @return string The SQL type
     */
    public function sqlType(): string
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
     */
    public function sqlPdoType(): int
    {
        return PDO::PARAM_BOOL;
    }

    /**
     * @see SelectablePropertyTrait::choices()
     */
    public function choices(): array
    {
        $val = $this->val();
        return [
            [
                'label'    => $this['trueLabel'],
                'selected' => (bool)$val,
                'value'    => 1,
            ],
            [
                'label'    => $this['falseLabel'],
                'selected' => !$val,
                'value'    => 0,
            ],
        ];
    }
}
