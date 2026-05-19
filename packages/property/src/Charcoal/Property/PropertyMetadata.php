<?php

declare(strict_types=1);

namespace Charcoal\Property;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Model\AbstractMetadata;

/**
 *
 */
class PropertyMetadata extends AbstractMetadata
{
    /**
     * The metadata identifier.
     */
    private ?string $ident = null;

    /**
     * The actual config data.
     *
     * @var array
     */
    public $data;

    /**
     * Set the metadata identifier.
     *
     * @param  string $ident The metadata identifier.
     * @throws InvalidArgumentException If identifier is not a string.
     */
    public function setIdent($ident): static
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                sprintf(
                    '[%s] Identifier must be a string; received %s',
                    static::class,
                    (get_debug_type($ident))
                )
            );
        }

        $this->ident = $ident;

        return $this;
    }

    /**
     * Retrieve the metadata identifier.
     *
     * @return string
     */
    public function ident(): ?string
    {
        return $this->ident;
    }
}
