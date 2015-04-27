<?php

namespace Charcoal\Loader;

class ObjectLoader extends AbstractLoader
{
    private $_ident = '';
    private $_obj;
    private $_source;

    public function set_source($source)
    {
        $this->_source = $source;
        return $this;
    }

    public function source()
    {
        return $this->_source;
    }

    /**
    * @param string $ident
    * @throws \InvalidArgumentException if the ident is not a string
    * @return MetadataLoader (Chainable)
    */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new \InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'() - Ident must be a string.');
        }
        $this->_ident = $ident;
        return $this;
    }

    public function ident()
    {
        return $this->_ident;
    }

    public function set_obj($obj)
    {
        $this->_obj = $obj;
        return $this;
    }

    public function obj()
    {
        return $this->_obj;
    }

    public function load($ident = null)
    {
        $data = $this->load_data($ident);
        if ($data !== false) {
            $this->obj()->set_flat_data($data);
        }
        return $this->obj();
    }

    public function load_data($ident = null)
    {
        // @todo: The query should call the object's properties to fetch any other needed data from other tables
        $q = '
		select
			 *
		from
			`'.$this->source()->table().'`
		where
			`'.$this->obj()->key().'`=:ident
		limit
			1';

        $sth = $this->source()->db()->prepare($q);
        $sth->bindParam(':ident', $ident);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $sth->execute();
        $data = $sth->fetch();

        return $data;
    }
}
