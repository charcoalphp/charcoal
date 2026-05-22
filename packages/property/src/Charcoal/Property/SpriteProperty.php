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
     */
    private ?string $sprite = null;

    /**
     * @var ViewInterface
     */
    private $view;

    public function type(): string
    {
        return 'sprite';
    }

    /**
     * Sets data on this entity.
     *
     * @uses   self::offsetSet()
     * @param  array $data Key-value array of data to append.
     */
    #[\Override]
    public function setData(array $data): static
    {
        parent::setData($data);

        $this->setChoices($this->buildChoicesFromSprite());

        return $this;
    }

    /**
     * Retrievs the spritesheet SVG file path.
     */
    public function getSprite(): ?string
    {
        return $this->sprite;
    }

    /**
     * Sets the spritesheet SVG file path.
     *
     * @param  string $sprite The SVG file path.
     * @throws InvalidArgumentException If the argument is not a string.
     */
    public function setSprite($sprite): static
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
    public function sqlType(): string
    {
        // Multiple strings are always stored as TEXT because they can hold multiple values
        if ($this['multiple']) {
            return 'TEXT';
        }

        return 'VARCHAR(255)';
    }

    public function sqlPdoType(): int
    {
        return PDO::PARAM_STR;
    }

    /**
     * Retrieve the available choice structures.
     *
     * @see    SelectablePropertyInterface::choices()
     */
    public function buildChoicesFromSprite(): array
    {
        $spritePath = $this['sprite'];

        if (!file_exists($spritePath)) {
            return [];
        }

        $spriteSheet = file_get_contents($spritePath);

        try {
            $xml = new SimpleXMLElement($spriteSheet);

            $choices = [];

            if (!property_exists($xml, 'symbol') || $xml->symbol === null) {
                if (property_exists($xml->defs, 'symbol') && $xml->defs->symbol !== null) {
                    $xml = $xml->defs;
                } else {
                    throw new RuntimeException(
                        'Missing <symbol> element(s)'
                    );
                }
            }

            $i = -1;
            foreach ($xml->symbol as $node) {
                $i++;

                $id = (string)$node->attributes()->id;

                if ($id === '' || $id === '0') {
                    $this->logger->warning(sprintf(
                        'Invalid SVG/XML spritesheet: Missing or empty ID attribute on: %s',
                        $node->asXML()
                    ), [
                        'property'     => $this['ident'],
                        'symbolIndex'  => $i,
                        'symbolID'     => null,
                        'spriteSource' => $spritePath,
                    ]);
                    continue;
                }

                if (isset($choices[$id])) {
                    $this->logger->warning(sprintf(
                        'Invalid SVG/XML spritesheet: Duplicate ID attribute: %s',
                        $id
                    ), [
                        'property'     => $this['ident'],
                        'symbolIndex'  => $i,
                        'symbolID'     => $id,
                        'spriteSource' => $spritePath,
                    ]);
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
                'property'     => $this['ident'],
                'spriteSource' => $spritePath,
                'exception'    => $e,
            ]);

            return [];
        }
    }

    /**
     * @param mixed $val     The value to to convert for display.
     * @return string
     *@see AbstractPropery::displayVal()
     */
    #[\Override]
    public function displayVal(mixed $val)
    {
        $val = parent::displayVal($val, $options);

        if ($val !== '') {
            $label = $this->translator()->trans('Selected sprite icon "%icon%"', [
                '%icon%' => $val,
            ]);

            $val = $this->view->renderTemplate(
                '<svg fill="currentColor" viewBox="0 0 25 25" height="40px" role="img" aria-label="' . $label . '">' .
                '<use xlink:href="{{# withBaseUrl }}{{ spritePathWithHash }}{{/ withBaseUrl }}"></use>' .
                '</svg>',
                [
                    'spritePathWithHash' => $this->getSprite() . '#' . $val,
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
            $val = $this->view->renderTemplate(
                '{{# withBaseUrl }}{{ spritePathWithHash }}{{/ withBaseUrl }}',
                [
                    'spritePathWithHash' => $this->getSprite() . '#' . $val,
                ]
            );
        }

        return $val;
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    #[\Override]
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
                'spritePathWithHash' => $this['sprite'] . '#' . $choice
            ];
        } elseif (is_array($choice)) {
            if (!isset($choice['value'])) {
                $choice['value'] = $choiceIdent;
            }

            if (!isset($choice['spritePathWithHash'])) {
                $choice['spritePathWithHash'] = $this['sprite'] . '#' . $choiceIdent;
            }

            if (!isset($choice['spritePathWithHash'])) {
                $choice['spritePathWithHash'] = $this['sprite'] . '#' . $choiceIdent;
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
