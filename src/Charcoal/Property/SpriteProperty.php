<?php

namespace Charcoal\Property;

use InvalidArgumentException;
use PDO;

/**
 * Sprite Property holds a reference to an external sprite svg.
 *
 * The object property implements the full `SelectablePropertyInterface` without using
 * its accompanying trait. (`set_choices`, `add_choice`, `choices`, `has_choice`, `choice`).
 */
class SpriteProperty extends AbstractProperty implements SelectablePropertyInterface
{
    use SelectablePropertyTrait;

    /**
     * The sprite svg to build the choices from.
     *
     * @var string
     */
    private $sprite;

    /**
     * The available selectable choices.
     *
     * This collection is built from selected {@see self::$objType}.
     *
     * @var array
     */
    protected $choices = [];

    /**
     * @return string
     */
    public function type()
    {
        return 'sprite';
    }

    /**
     * @return string
     */
    public function sprite()
    {
        return $this->sprite;
    }

    /**
     * @param string $sprite The sprite svg.
     * @throws InvalidArgumentException If the object type is not a string.
     * @return self
     */
    public function setSprite($sprite)
    {
        if (!is_string($sprite)) {
            throw new InvalidArgumentException(
                'Property sprite type ("sprite") must be a string.'
            );
        }

        $this->sprite = $sprite;

        return $this;
    }

    /**
     * @return string
     */
    public function sqlExtra()
    {
        return '';
    }

    /**
     * Get the SQL type (Storage format)
     *
     * Stored as `VARCHAR` for maxLength under 255 and `TEXT` for other, longer strings
     *
     * @return string The SQL type
     */
    public function sqlType()
    {
        // Multiple strings are always stored as TEXT because they can hold multiple values
        if ($this->multiple()) {
            return 'TEXT';
        }

        return 'VARCHAR(255)';
    }

    /**
     * @return integer
     */
    public function sqlPdoType()
    {
        return PDO::PARAM_STR;
    }

    /**
     * Retrieve the available choice structures.
     *
     * @see    SelectablePropertyInterface::choices()
     * @return array
     */
    public function choices()
    {
        $sprite = $this->sprite();
        if (!file_exists($sprite)) {
            return [];
        }

        $spriteString = file_get_contents($sprite);

        $xml = new \SimpleXMLElement($spriteString);

        $choices = [];

        foreach ($xml->symbol as $ident => $node) {
            $id = (string)$node->attributes()->id;

            $choices[$sprite.'#'.$id] = $id;
        }

        return $choices;
    }
}
