<?php

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
     *
     * @var string
     */
    private $ident;

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
     * @return self
     */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                sprintf(
                    '[%s] Identifier must be a string; received %s',
                    get_called_class(),
                    (is_object($ident) ? get_class($ident) : gettype($ident))
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
    public function ident()
    {
        return $this->ident;
    }
}
