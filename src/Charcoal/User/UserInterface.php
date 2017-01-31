<?php

namespace Charcoal\User;

// Dependency from 'charcoal-core'
use Charcoal\Object\ContentInterface;

/**
 * User Interface, based on charcoal/object/content-interface.
 */
interface UserInterface extends ContentInterface
{
    /**
     * @return string
     */
    public static function sessionKey();

    /**
     * Force a lowercase username
     *
     * @param string $username The username (also the login name).
     * @return UserInterface Chainable
     */
    public function setUsername($username);

    /**
     * The username is also used as login name and main identifier (key).
     *
     * @return string
     */
    public function username();

    /**
     * @param string $email The user email.
     * @return UserInterface Chainable
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function email();

    /**
     * @param string|null $password The user password. Encrypted in storage.
     * @return UserInterface Chainable
     */
    public function setPassword($password);

    /**
     * @return string
     */
    public function password();

    /**
     * @param string|string[]|null $roles The ACL roles this user belongs to.
     * @throws InvalidArgumentException If the roles argument is invalid.
     * @return UserInterface Chainable
     */
    public function setRoles($roles);

    /**
     * @return string[]
     */
    public function roles();

    /**
     * @param boolean $active The active flag.
     * @return UserInterface Chainable
     */
    public function setActive($active);

    /**
     * @return boolean
     */
    public function active();

    /**
     * @param string|\DateTimeInterface $ts The last login date.
     * @return UserInterface Chainable
     */
    public function setLastLoginDate($ts);

    /**
     * @return \DateTimeInterface|null
     */
    public function lastLoginDate();

    /**
     * @param string|integer|null $ip The last login IP address.
     * @return UserInterface Chainable
     */
    public function setLastLoginIp($ip);

    /**
     * Get the last login IP in x.x.x.x format
     * @return string
     */
    public function lastLoginIp();

    /**
     * @param string|\DateTimeInterface $ts The last password date.
     * @return UserInterface Chainable
     */
    public function setLastPasswordDate($ts);

    /**
     * @return \DateTimeInterface|null
     */
    public function lastPasswordDate();

    /**
     * @param integer|string|null $ip The last password IP.
     * @return UserInterface Chainable
     */
    public function setLastPasswordIp($ip);

    /**
     * Get the last password change IP in x.x.x.x format.
     *
     * @return string
     */
    public function lastPasswordIp();

    /**
     * @param string $token The login token.
     * @return UserInterface Chainable
     */
    public function setLoginToken($token);

    /**
     * @return string
     */
    public function loginToken();

    /**
     * Reset the password.
     *
     * Encrypt the password and re-save the object in the database.
     * Also updates the last password date & ip.
     *
     * @param string $plainPassword The plain (non-encrypted) password to reset to.
     * @return UserInterface Chainable
     */
    public function resetPassword($plainPassword);
}
