<?php

namespace Charcoal\Property;

use Exception;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use SimpleXMLElement;

// from 'charcoal-view'
use Charcoal\View\ViewInterface;

// from 'pimple'
use Pimple\Container;

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
     * @var ViewInterface
     */
    private $view;

    /**
     * @return string
     */
    public function type()
    {
        return 'sprite';
    }

    /**
     * Sets data on this entity.
     *
     * @uses   self::offsetSet()
     * @param  array $data Key-value array of data to append.
     * @return self
     */
    public function setData(array $data)
    {
        parent::setData($data);

        $this->setChoices($this->buildChoicesFromSprite());

        return $this;
    }

    /**
     * Retrievs the spritesheet SVG file path.
     *
     * @return string|null
     */
    public function getSprite()
    {
        return $this->sprite;
    }

    /**
     * Sets the spritesheet SVG file path.
     *
     * @param  string $sprite The SVG file path.
     * @throws InvalidArgumentException If the argument is not a string.
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
     * Get the SQL type (Storage format)
     *
     * Stored as `VARCHAR` for maxLength under 255 and `TEXT` for other, longer strings
     *
     * @return string The SQL type
     */
    public function sqlType()
    {
        // Multiple strings are always stored as TEXT because they can hold multiple values
        if ($this['multiple']) {
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
    public function buildChoicesFromSprite()
    {
        $spritePath = $this['sprite'];

        if (!file_exists($spritePath)) {
            return [];
        }

        $spriteSheet = file_get_contents($spritePath);

        try {
            $xml = new SimpleXMLElement($spriteSheet);

            $choices = [];

            if (isset($xml->defs)) {
                $xml = $xml->defs;
            }

            if (!isset($xml->symbol)) {
                throw new RuntimeException(
                    'Missing <symbol> element(s)'
                );
            }

            foreach ($xml->symbol as $node) {
                $id = (string) $node->attributes()->id;

                if (!$id) {
                    $this->logger->warning(sprintf(
                        'Invalid SVG/XML spritesheet: Missing or empty ID attribute on: %s',
                        $node->asXML()
                    ));
                    continue;
                }

                if (isset($choices[$id])) {
                    $this->logger->warning(sprintf(
                        'Invalid SVG/XML spritesheet: Duplicate ID attribute: %s',
                        $id
                    ));
                    continue;
                }

                $choices[$id] = $id;
            }

            return $choices;
        } catch (Exception $e) {
            $this->logger->error(sprintf(
                'Invalid SVG/XML spritesheet: %s',
                $e->getMessage()
            ), [
                'exception' => $e,
            ]);

            return [];
        }
    }

    /**
     * @param  mixed $val     The value to to convert for display.
     * @param  array $options Optional display options.
     * @see AbstractPropery::displayVal()
     * @return string
     */
    public function displayVal($val, array $options = [])
    {
        $val = parent::displayVal($val, $options);

        if ($val !== '') {
            $label = $this->translator()->trans('Selected sprite icon "%icon%"', [
                '%icon%' => $val,
            ]);

            $val = $this->view->render(
                '<svg fill="currentColor" viewBox="0 0 25 25" height="40px" role="img" aria-label="'.$label.'">'.
                '<use xlink:href="{{# withBaseUrl }}{{ spritePathWithHash }}{{/ withBaseUrl }}"></use>'.
                '</svg>',
                [
                    'spritePathWithHash' => $this->getSprite().'#'.$val,
                ]
            );
        }

        return $val;
    }

    /**
     * @param  mixed $val The value to to convert as path.
     * @return string
     */
    public function spriteVal($val)
    {
        if ($val !== '') {
            $val = $this->view->render(
                '{{# withBaseUrl }}{{ spritePathWithHash }}{{/ withBaseUrl }}',
                [
                    'spritePathWithHash' => $this->getSprite().'#'.$val,
                ]
            );
        }

        return $val;
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->view = $container['view'];
    }

    /**
     * Parse the given value into a choice structure.
     *
     * @param  string|array $choice      A string representing the choice label or a structure.
     * @param  string       $choiceIdent The choice identifier (will be key / default ident).
     * @throws InvalidArgumentException If the choice identifier is not a string.
     * @return array Returns a choice structure.
     */
    protected function parseChoice($choice, $choiceIdent)
    {
        if (!is_string($choiceIdent)) {
            throw new InvalidArgumentException(
                'Choice identifier must be a string.'
            );
        }

        if (is_string($choice)) {
            $choice = [
                'value'              => $choiceIdent,
                'label'              => $this->translator()->translation($choiceIdent),
                'spritePathWithHash' => $this['sprite'].'#'.$choice
            ];
        } elseif (is_array($choice)) {
            if (!isset($choice['value'])) {
                $choice['value'] = $choiceIdent;
            }

            if (!isset($choice['spritePathWithHash'])) {
                $choice['spritePathWithHash'] = (string)$this['sprite'].'#'.$choiceIdent;
            }

            if (!isset($choice['spritePathWithHash'])) {
                $choice['spritePathWithHash'] = (string)$this['sprite'].'#'.$choiceIdent;
                $choice['label']              = $this->translator()->translation($choiceIdent);
            }
        } else {
            throw new InvalidArgumentException(
                'Choice must be a string or an array.'
            );
        }

        return $choice;
    }
}
