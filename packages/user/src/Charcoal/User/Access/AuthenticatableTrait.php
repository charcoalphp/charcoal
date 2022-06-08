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
     * Retrieve the login token for the user.
     *
     * @return string|null
     */
    public function getAuthLoginToken()
    {
        $key = $this->getAuthLoginTokenKey();
        return $this[$key];
    }

    /**
     * Set the token value for the login token.
     *
     * @param  string $value The token value.
     * @return void
     */
    public function setAuthLoginToken($value)
    {
        $key = $this->getAuthLoginTokenKey();
        $this[$key] = $value;
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

    /**
     * Retrieve the name of the login token for the user.
     *
     * Typically, "login_token" or "remember_token".
     *
     * @return string
     */
    abstract public function getAuthLoginTokenKey();
}
