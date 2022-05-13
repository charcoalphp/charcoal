<?php

namespace Charcoal\User;

// From 'charcoal-user'
use Charcoal\User\Access\AuthenticatableInterface;

/**
 * Authenticator Interface
 */
interface AuthenticatorInterface
{
    /**
     * Determine if the current user is authenticated.
     *
     * @return boolean
     */
    public function check();

    /**
     * Retrieve the currently authenticated user.
     *
     * The method will attempt to authenticate a user.
     *
     * @return AuthenticatableInterface|null
     */
    public function user();

    /**
     * Retrieve the ID for the currently authenticated user.
     *
     * The method will attempt to authenticate a user.
     *
     * @return mixed
     */
    public function userId();

    /**
     * Log a user into the application.
     *
     * @param  AuthenticatableInterface $user     The authenticated user to log in.
     * @param  boolean                  $remember Whether to "remember" the user or not.
     * @return boolean
     */
    public function login(AuthenticatableInterface $user, $remember = false);

    /**
     * Log the user out of the application.
     *
     * @return boolean Logged out or not.
     */
    public function logout();

    /**
     * Attempt to authenticate a user by session or token.
     *
     * The user is authenticated via _session ID_ or _auth token_.
     *
     * @return AuthenticatableInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    public function authenticate();

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  string $identifier The login ID, part of necessary credentials.
     * @param  string $password   The password, part of necessary credentials.
     * @return AuthenticatableInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    public function authenticateByPassword($identifier, $password);

    /**
     * Validate the user authentication state is okay.
     *
     * For example, inactive users can not authenticate.
     *
     * @param  AuthenticatableInterface $user The user to validate.
     * @return boolean
     */
    public function validateAuthentication(AuthenticatableInterface $user);
}
