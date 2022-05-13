<?php

namespace Charcoal\User\Acl;

use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;

// From 'charcoal-core'
use Charcoal\Model\AbstractModel;

/**
 * ACL Roles define hierarchical allowed and denied permissions.
 *
 * They can be attached to user accounts for fine-grained permission control.
 */
class Role extends AbstractModel
{
    use TranslatorAwareTrait;

    /**
     * @var string|null $ident
     */
    public $ident;

    /**
     * The parent ACL role.
     *
     * This role will inherit all of its parent's permissions.
     *
     * @var string|null $parent
     */
    public $parent;

    /**
     * The user-friendly name.
     *
     * @var \Charcoal\Translator\Translation|null
     */
    public $name;

    /**
     * List of explicitely allowed permissions.
     *
     * @var string[]|null $allowed
     */
    public $allowed;

    /**
     * List of explicitely denied permissions.
     *
     * @var string[]|null $denied
     */
    public $denied;

    /**
     * @var boolean
     */
    private $superuser = false;

    /**
     * @var integer
     */
    private $position;

    /**
     * ACL Role can be used as a string (ident).
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->ident === null) {
            return '';
        }
        return $this->ident;
    }

    /**
     * @return string
     */
    public function key()
    {
        return 'ident';
    }

    /**
     * @param  string|Role $parent Role's parent.
     * @return self
     */
    public function setParent($parent)
    {
        if ($parent instanceof self) {
            $parent = $parent['ident'];
        }

        $this->parent = $parent;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param  mixed $name The user-friendly name of this role.
     * @return self
     */
    public function setName($name)
    {
        $this->name = $this->p('name')->parseVal($name);
        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  mixed $allowed The allowed permissions for this role.
     * @return self
     */
    public function setAllowed($allowed)
    {
        $allowed = $this->p('allowed')->parseVal($allowed);
        if (is_array($allowed)) {
            $allowed = array_filter(array_map('trim', $allowed));
        }

        $this->allowed = $allowed;
        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getAllowed()
    {
        return $this->allowed;
    }

    /**
     * @param  mixed $denied The denied permissions for this role.
     * @return self
     */
    public function setDenied($denied)
    {
        $denied = $this->p('denied')->parseVal($denied);
        if (is_array($denied)) {
            $denied = array_filter(array_map('trim', $denied));
        }

        $this->denied = $denied;
        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getDenied()
    {
        return $this->denied;
    }

    /**
     * @param  boolean $isSuper The superuser flag.
     * @return self
     */
    public function setSuperuser($isSuper)
    {
        $this->superuser = !!$isSuper;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getSuperuser()
    {
        return $this->superuser;
    }

    /**
     * @param  integer|string|null $position The role's ordering position.
     * @return self
     */
    public function setPosition($position)
    {
        $this->position = (int)$position;
        return $this;
    }

    /**
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param  Container $container Pimple DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setTranslator($container['translator']);
    }
}
