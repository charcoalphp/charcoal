<?php

namespace Charcoal\Loader;

use \Exception;
use \InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Model\ModelInterface;

/**
*
*/
class ObjectLoader
{
    /**
    * @var string $ident
    */
    private $ident = '';
    /**
    * @var ModelInterface $obj
    */
    private $obj;
    /**
    * @var SourceInterface $source
    */
    private $source;

    /**
    * @param mixed $source The loader source.
    * @return ObjectLoader Chainable
    */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
    * @return mixed
    */
    public function source()
    {
        return $this->source;
    }

    /**
    * @param ModelInterface $model The loader / source model.
    * @return Source Chainable
    */
    public function setModel(ModelInterface $model)
    {
        $this->model = $model;
        $this->set_source($model->source());
        return $this;
    }

    /**
    * @throws Exception If not model was previously set.
    * @return Model
    */
    public function model()
    {
        if ($this->model === null) {
            throw new Exception('No model set.');
        }
        return $this->model;
    }

    /**
    * @param string $ident The loader ident.
    * @throws InvalidArgumentException If the ident is not a string.
    * @return MetadataLoader Chainable
    */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                __CLASS__.'::'.__FUNCTION__.'() - Ident must be a string.'
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
    * @param ModelInterface $obj The prototype object.
    * @return ObjectLoader Chainable
    */
    public function setObj(ModelInterface $obj)
    {
        $this->obj = $obj;
        return $this;
    }

    /**
    * @return ModelInterface
    */
    public function obj()
    {
        return $this->obj;
    }

    /**
    * @param string|null $ident Optional. The ident.
    * @return ModelInterface
    */
    public function load($ident = null)
    {
        $data = $this->loadData($ident);

        if ($data !== false) {
            $this->obj()->setFlatData($data);
        }

        return $this->obj();
    }

    /**
    * @param string|null $ident Optional. The ident.
    * @return array
    */
    public function loadData($ident = null)
    {
        /**
        * @todo The query should call the object's properties to fetch
        *       any other needed data from other tables.
        */
        $q = '
        SELECT
             *
        FROM
            `'.$this->source()->table().'`
        WHERE
            `'.$this->obj()->key().'`=:ident
        LIMIT
            1';

        $sth = $this->source()->db()->prepare($q);
        $sth->bindParam(':ident', $ident);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $sth->execute();
        $data = $sth->fetch();

        return $data;
    }
}
