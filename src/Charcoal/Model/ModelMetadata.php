<?php

namespace Charcoal\Model;

use \Charcoal\Metadata\AbstractMetadata as AbstractMetadata;

use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Helper\Cache as Cache;

class ModelMetadata extends AbstractMetadata implements \ArrayAccess
{

    /**
     * @var string $_ident
     */
    private $_ident;

    /**
    * The actual config data
    * @var array $data
    */
    public $data;

    /**
    * @param string $ident
    * @throws \InvalidArgumentException if ident is not a string
    * @return ModelMetadata Chainable
    */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new \InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'Ident must be a string');
        }
        $this->_ident = $ident;

        return $this;
    }

    /**
    * @return string
    */
    public function ident()
    {
        return $this->_ident;
    }

}
