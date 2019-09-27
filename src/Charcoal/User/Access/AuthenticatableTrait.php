<?php

namespace Charcoal\User\Access;

/**
 * An implementation, as Trait, of the {@see \Charcoal\User\Access\AuthenticatableInterface}.
 *
 * Trait expects class to implement {@see \ArrayAccess}.
 */
trait AuthenticatableTrait
{
    /**
     * Retrieve the unique ID for the user.
     *
     * @return mixed
     */
    public function getAuthId()
    {
        $key = $this->getAuthIdKey();
        return $this[$key];
    }

    /**
     * Retrieve the login ID for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        $key = $this->getAuthIdentifierKey();
        return $this[$key];
    }

    /**
     * Retrieve the password for the user.
     *
     * @return string|null
     */
    public function getAuthPassword()
    {
        $key = $this->getAuthPasswordKey();
        return $this[$key];
    }

    /**
     * Retrieve the name of the unique ID for the user.
     *
     * @return string
     */
    abstract public function getAuthIdKey();

    /**
     * Retrieve the name of the login username for the user.
     *
     * @return string
     */
    abstract public function getAuthIdentifierKey();

    /**
     * Retrieve the name of the login password for the user.
     *
     * Typically, "password".
     *
     * @return string
     */
    abstract public function getAuthPasswordKey();
}
