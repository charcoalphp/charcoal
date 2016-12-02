<?php

namespace Charcoal\Property\Structure;

use \InvalidArgumentException;

// From 'charcoal-core'
use \Charcoal\Model\AbstractMetadata;

/**
 *
 */
class StructureMetadata extends AbstractMetadata
{
    /**
     * The metadata identifier.
     *
     * @var string|null
     */
    private $ident;

    /**
     * Set the metadata identifier.
     *
     * @param  string $ident The metadata identifier.
     * @throws InvalidArgumentException If identifier is not a string.
     * @return StructureMetadata Chainable
     */
    public function setIdent($ident)
    {
        if ($ident === null) {
            $this->ident = null;
            return $this;
        }

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
     * @return string|null
     */
    public function ident()
    {
        return $this->ident;
    }
}
