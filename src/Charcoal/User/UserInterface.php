<?php

namespace Charcoal\User;

// From 'charcoal-object'
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
     * @param string $email The user email.
     * @return self
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string|null $password The user password. Encrypted in storage.
     * @return self
     */
    public function setPassword($password);

    /**
     * @return string
     */
    public function getPassword();

    /**
     * @param  string|null $name The user's display name.
     * @return self
     */
    public function setDisplayName($name);

    /**
     * @return string|null
     */
    public function getDisplayName();

    /**
     * @param string|string[]|null $roles The ACL roles this user belongs to.
     * @throws InvalidArgumentException If the roles argument is invalid.
     * @return self
     */
    public function setRoles($roles);

    /**
     * @return string[]
     */
    public function getRoles();

    /**
     * @param string|\DateTimeInterface $ts The last login date.
     * @return self
     */
    public function setLastLoginDate($ts);

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastLoginDate();

    /**
     * @param string|integer|null $ip The last login IP address.
     * @return self
     */
    public function setLastLoginIp($ip);

    /**
     * Get the last login IP in x.x.x.x format
     * @return string
     */
    public function getLastLoginIp();

    /**
     * @param string|\DateTimeInterface $ts The last password date.
     * @return self
     */
    public function setLastPasswordDate($ts);

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastPasswordDate();

    /**
     * @param integer|string|null $ip The last password IP.
     * @return self
     */
    public function setLastPasswordIp($ip);

    /**
     * Get the last password change IP in x.x.x.x format.
     *
     * @return string
     */
    public function getLastPasswordIp();

    /**
     * @param string $token The login token.
     * @return self
     */
    public function setLoginToken($token);

    /**
     * @return string
     */
    public function getLoginToken();

    /**
     * Reset the password.
     *
     * Encrypt the password and re-save the object in the database.
     * Also updates the last password date & ip.
     *
     * @param string $plainPassword The plain (non-encrypted) password to reset to.
     * @return self
     */
    public function resetPassword($plainPassword);

    /**
     * Structure
     *
     * Get the user preferences
     *
     * @return array|mixed
     */
    public function preferences();
}
