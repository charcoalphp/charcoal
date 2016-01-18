<?php

namespace Charcoal\Model;

use \InvalidArgumentException;

use \Charcoal\Model\AbstractMetadata;

class ModelMetadata extends AbstractMetadata implements \ArrayAccess
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
    * The actual config data
    * @var array $data
    */
//    public $data;

    /**
    * @param string $ident
    * @throws InvalidArgumentException if ident is not a string
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
    * @param array $sources
    * @throws InvalidArgumentException
    * @return ModelMetadata Chainable
    */
    public function setSources($sources)
    {
        if (!is_array($sources)) {
            throw new InvalidArgumentException(
                'Sources must be an array.'
            );
        }
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
    * @param string $sourceIdent
    * @param mixed  $source
    * @return ModelMetadata Chainable
    */
    public function addSource($sourceIdent, $source)
    {
        $this->sources[$sourceIdent] = $source;
        return $this;
    }

    /**
    * @param string $sourceIdent
    * @return mixed
    */
    public function source($sourceIdent)
    {
        return $this->sources[$sourceIdent];
    }

    /**
    * @param string $defaultSource
    * @throws InvalidArgumentException
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
