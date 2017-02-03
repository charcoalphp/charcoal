<?php

namespace Charcoal\User\Acl;

use InvalidArgumentException;

// Dependency from 'charcoal-core'
use Charcoal\Model\AbstractModel;

/**
 * ACL Roles define hierarchical allowed and denied permissions.
 *
 * They can be attached to user accounts for fine-grained permission control.
 */
class Role extends AbstractModel
{
    const SEPARATOR = ',';

    /**
     * @var string|null $ident
     */
    public $ident;

    /**
     * The parent ACL role.
     *
     * This role will inherit all of its parent's permissions.
     *
     * @var string $parent
     */
    public $parent;

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
     * @param string|Role $parent Role's parent.
     * @return Role Chainable
     */
    public function setParent($parent)
    {
        $this->parent = (string)$parent;
        return $this;
    }

    /**
     * @return string
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * @param string[]|string|null $allowed The allowed permissions for this role.
     * @throws InvalidArgumentException If the passed arguments is not an array, null, or a comma-separated string.
     * @return Role Chainable
     */
    public function setAllowed($allowed)
    {
        if ($allowed === null) {
            $this->allowed = null;
            return $this;
        }

        if (is_string($allowed)) {
            $allowed = explode(self::SEPARATOR, $allowed);
            $allowed = array_map('trim', $allowed);
        }
        if (!is_array($allowed)) {
            throw new InvalidArgumentException(
                'Invalid allowed value. Must be an array, null, or a comma-separated string.'
            );
        }
        $this->allowed = $allowed;
        return $this;
    }

    /**
     * @return string[]|null
     */
    public function allowed()
    {
        return $this->allowed;
    }

    /**
     * @param string[]|string|null $denied The denied permissions for this role.
     * @throws InvalidArgumentException If the passed arguments is not an array, null, or a comma-separated string.
     * @return Role Chainable
     */
    public function setDenied($denied)
    {
        if ($denied === null) {
            $this->denied = null;
            return $this;
        }

        if (is_string($denied)) {
            $denied = explode(self::SEPARATOR, $denied);
            $denied = array_walk('trim', $denied);
        }
        if (!is_array($denied)) {
            throw new InvalidArgumentException(
                'Invalid denied value. Must be an array, null, or a comma-separated string.'
            );
        }
        $this->denied = $denied;
        return $this;
    }

    /**
     * @return string[]|null
     */
    public function denied()
    {
        return $this->denied;
    }

    /**
     * @param boolean $isSuper The superuser flag.
     * @return Role Chainable
     */
    public function setSuperuser($isSuper)
    {
        $this->superuser = !!$isSuper;
        return $this;
    }

    /**
     * @return boolean
     */
    public function superuser()
    {
        return $this->superuser;
    }

    /**
     * @param integer|string|null $position The role's ordering position.
     * @return Role Chainable
     */
    public function setPosition($position)
    {
        $this->position = (int)$position;
        return $this;
    }

    /**
     * @return integer
     */
    public function position()
    {
        return $this->position;
    }
}
