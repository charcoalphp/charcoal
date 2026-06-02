<?php

namespace Charcoal\Model;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Model\AbstractMetadata;

/**
 *
 */
class ModelMetadata extends AbstractMetadata
{
    /**
     * The metadata identifier.
     */
    private ?string $ident = null;

    /**
     * The model's sources.
     *
     * @var array
     */
    private $sources;

    /**
     * The model's default source.
     */
    private ?string $defaultSource = null;

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

    /**
     * @param array $sources The available sources for this model.
     */
    public function setSources(array $sources): static
    {
        foreach ($sources as $sourceIdent => $source) {
            $this->addSource($sourceIdent, $source);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function sources()
    {
        return $this->sources;
    }

    /**
     * @param string $sourceIdent The source identifier.
     * @param mixed  $source      The source data.
     */
    public function addSource($sourceIdent, $source): static
    {
        $this->sources[$sourceIdent] = $source;
        return $this;
    }

    /**
     * @param string $sourceIdent The source identifier to get.
     * @return mixed
     */
    public function source($sourceIdent)
    {
        if (!isset($this->sources[$sourceIdent])) {
            throw new InvalidArgumentException(sprintf('Trying to access undefined source "%s"', $sourceIdent));
        }

        return $this->sources[$sourceIdent];
    }

    /**
     * @param string $defaultSource The default source identifier.
     * @throws InvalidArgumentException If the argument is not a string.
     */
    public function setDefaultSource($defaultSource): static
    {
        if (!is_string($defaultSource)) {
            throw new InvalidArgumentException(
                'Default source needs to be a string.'
            );
        }
        $this->defaultSource = $defaultSource;
        return $this;
    }

    /**
     * @return string
     */
    public function defaultSource(): ?string
    {
        return $this->defaultSource;
    }
}
