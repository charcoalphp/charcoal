<?php

namespace Charcoal\Model;

use \InvalidArgumentException;

use \Charcoal\Model\AbstractMetadata;

/**
 *
 */
class ModelMetadata extends AbstractMetadata
{
    /**
     * @var string $Ident
     */
    private $ident;

    /**
     * @var array $Sources
     */
    private $sources;

    /**
     * @var string $defaultSource
     */
    private $defaultSource;

    /**
     * @param string $ident The object meta identifier.
     * @throws InvalidArgumentException If ident is not a string.
     * @return ModelMetadata Chainable
     */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                __CLASS__.'::'.__FUNCTION__.'Ident must be a string.'
            );
        }
        $this->ident = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * @param array $sources The available sources for this model.
     * @return ModelMetadata Chainable
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
     * @return ModelMetadata Chainable
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
     * @return ModelMetadata Chainable
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
