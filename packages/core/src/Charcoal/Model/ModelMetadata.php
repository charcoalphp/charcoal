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
     *
     * @var string
     */
    private $ident;

    /**
     * The model's sources.
     *
     * @var array
     */
    private $sources;

    /**
     * The model's default source.
     *
     * @var string
     */
    private $defaultSource;

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

    /**
     * @param array $sources The available sources for this model.
     * @return self
     */
    public function setSources(array $sources)
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
     * @return self
     */
    public function addSource($sourceIdent, $source)
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
        return $this->sources[$sourceIdent];
    }

    /**
     * @param string $defaultSource The default source identifier.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setDefaultSource($defaultSource)
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
    public function defaultSource()
    {
        return $this->defaultSource;
    }
}
