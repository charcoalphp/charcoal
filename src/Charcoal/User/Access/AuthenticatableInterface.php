<?php

namespace Charcoal\User\Access;

/**
 * Authenticatable Interface
 *
 * Defines common methods for identifying core properties for authenticating a model.
 *
 * @charcoal-metadata
 */
interface AuthenticatableInterface
{
    /**
     * Retrieve the name of the unique ID for the user.
     *
     * Typically, "id".
     *
     * @return string
     */
    public function getAuthIdKey();

    /**
     * Retrieve the unique ID for the user.
     *
     * @return mixed
     */
    public function getAuthId();

    /**
     * Retrieve the name of the login identifier for the user.
     *
     * Typically, "email", "phone", "username", or "id".
     *
     * @return string
     */
    public function getAuthIdentifierKey();

    /**
     * Retrieve the login identifier for the user.
     *
     * Typically, an email address, telephone number, username, or unique ID.
     *
     * @return mixed
     */
    public function getAuthIdentifier();

    /**
     * Retrieve the name of the login password for the user.
     *
     * Typically, "password".
     *
     * @return string
     */
    public function getAuthPasswordKey();

    /**
     * Retrieve the password for the user.
     *
     * @return string|null
     */
    public function getAuthPassword();
}
