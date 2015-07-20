<?php
/**
 * Charcoal Object
 *
 * @package    Charcoal
 * @subpackage core
 *
 * @copyright (c) Locomotive 2007-2012
 * @author    Mathieu Ducharme <mat@locomotive.ca>
 * @version   2012-06-28
 * @since     Version 2012-03-01
 * @license   LGPL
 */

namespace Charcoal\Model;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\Core\IndexableInterface as IndexableInterface;
use \Charcoal\Core\IndexableTrait as IndexableTrait;

use \Charcoal\Model\Model as Model;
use \Charcoal\Source\DatabaseSource as Database;

use \Charcoal\Loader\ObjectLoader as ObjectLoader;

class Object extends Model implements IndexableInterface
{
    use IndexableTrait;

    const DEFAULT_KEY = 'id';

    /**
    * @var boolean $_active
    */
    private $_active = true;

    /**
    * @param array $data Optional
    */
    public function __construct(array $data = [])
    {
        // Use Model constructor...
        parent::__construct();

        $metadata = $this->metadata();
        if (!isset($data['key'])) {
            $data['key'] = (isset($metadata['key']) ? $metadata['key'] : self::DEFAULT_KEY);
        }

        $this->set_data($data);
    }

    /**
    * @param array $data
    * @return Object Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        $this->set_indexable_data($data);
        if (isset($data['active'])) {
            $this->set_key($data['active']);
        }

        return $this;
    }

    /**
    * @param boolean $active
    * @throws InvalidArgumentException
    * @return Object Chainable
    */
    public function set_active($active)
    {
        if (!is_bool($active)) {
            throw new InvalidArgumentException('Active parameter must be a boolean.');
        }
        $this->_active = $active;
        return $this;
    }

    /**
    * @return boolean
    */
    public function active()
    {
        return $this->_active;
    }

    /**
    * @return ObjectLoader
    */
    public function loader()
    {
        $metadata = $this->metadata();

        $source = new Database();
        $source->set_table('charcoal_admin_users');

        $loader = new ObjectLoader();
        $loader->set_obj($this);
        $loader->set_source($source);

        return $loader;
    }

    /**
    * @param string|null $ident
    * @return void
    */
    public function load($ident = null)
    {
        if ($ident === null) {
            $ident = $this->id();
        }
        $loader = $this->loader();
        $data = $loader->load_data($ident);
    }
}
