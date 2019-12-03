<?php

namespace Charcoal\User;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-core'
use Charcoal\Validator\ValidatorInterface;

// From 'charcoal-object'
use Charcoal\Object\Content;

// From 'charcoal-user'
use Charcoal\User\Access\AuthenticatableInterface;
use Charcoal\User\Access\AuthenticatableTrait;

/**
 * Full implementation, as abstract class, of the `UserInterface`.
 */
abstract class AbstractUser extends Content implements
    AuthenticatableInterface,
    UserInterface
{
    use AuthenticatableTrait;

    /**
     * The email address should be unique and mandatory.
     *
     * It is also used as the login name.
     *
     * @var string
     */
    private $email;

    /**
     * The password is stored encrypted in the (database) storage.
     *
     * @var string|null
     */
    private $password;

    /**
     * The display name serves as a human-readable identifier for the user.
     *
     * @var string|null
     */
    private $displayName;

    /**
     * Roles define a set of tasks a user is allowed or denied from performing.
     *
     * @var string[]
     */
    private $roles = [];

    /**
     * The timestamp of the latest (successful) login.
     *
     * @var DateTimeInterface|null
     */
    private $lastLoginDate;

    /**
     * The IP address during the latest (successful) login.
     *
     * @var string|null
     */
    private $lastLoginIp;

    /**
     * The timestamp of the latest password change.
     *
     * @var DateTimeInterface|null
     */
    private $lastPasswordDate;

    /**
     * The IP address during the latest password change.
     *
     * @var string|null
     */
    private $lastPasswordIp;

    /**
     * The token value for the "remember me" session.
     *
     * @var string|null
     */
    private $loginToken;

    /**
     * The user preferences.
     *
     * @var mixed
     */
    private $preferences;

    /**
     * @param  string $email The user email.
     * @throws InvalidArgumentException If the email is not a string.
     * @return self
     */
    public function setEmail($email)
    {
        if (!is_string($email)) {
            throw new InvalidArgumentException(
                'Set user email: Email must be a string'
            );
        }

        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param  string|null $password The user password. Encrypted in storage.
     * @throws InvalidArgumentException If the password is not a string (or null, to reset).
     * @return self
     */
    public function setPassword($password)
    {
        if ($password === null) {
            $this->password = $password;
        } elseif (is_string($password)) {
            $this->password = $password;
        } else {
            throw new InvalidArgumentException(
                'Set user password: Password must be a string'
            );
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param  string|null $name The user's display name.
     * @return self
     */
    public function setDisplayName($name)
    {
        $this->displayName = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param  string|string[]|null $roles The ACL roles this user belongs to.
     * @throws InvalidArgumentException If the roles argument is invalid.
     * @return self
     */
    public function setRoles($roles)
    {
        if (empty($roles) && !is_numeric($roles)) {
            $this->roles = [];
            return $this;
        }

        if (is_string($roles)) {
            $roles = explode(',', $roles);
        }

        if (!is_array($roles)) {
            throw new InvalidArgumentException(
                'Roles must be a comma-separated string or an array'
            );
        }

        $this->roles = array_filter(array_map('trim', $roles), 'strlen');

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param  string|DateTimeInterface|null $lastLoginDate The last login date.
     * @throws InvalidArgumentException If the ts is not a valid date/time.
     * @return self
     */
    public function setLastLoginDate($lastLoginDate)
    {
        if ($lastLoginDate === null) {
            $this->lastLoginDate = null;
            return $this;
        }

        if (is_string($lastLoginDate)) {
            try {
                $lastLoginDate = new DateTime($lastLoginDate);
            } catch (Exception $e) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid login date (%s)',
                    $e->getMessage()
                ), 0, $e);
            }
        }

        if (!($lastLoginDate instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Last Login Date" value. Must be a date/time string or a DateTime object.'
            );
        }

        $this->lastLoginDate = $lastLoginDate;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getLastLoginDate()
    {
        return $this->lastLoginDate;
    }

    /**
     * @param  string|integer|null $ip The last login IP address.
     * @throws InvalidArgumentException If the IP is not an IP string, an integer, or null.
     * @return self
     */
    public function setLastLoginIp($ip)
    {
        if ($ip === null) {
            $this->lastLoginIp = null;
            return $this;
        }

        if (is_int($ip)) {
            $ip = long2ip($ip);
        }

        if (!is_string($ip)) {
            throw new InvalidArgumentException(
                'Invalid IP address'
            );
        }

        $this->lastLoginIp = $ip;

        return $this;
    }

    /**
     * Get the last login IP in x.x.x.x format
     *
     * @return string|null
     */
    public function getLastLoginIp()
    {
        return $this->lastLoginIp;
    }

    /**
     * @param  string|DateTimeInterface|null $lastPasswordDate The last password date.
     * @throws InvalidArgumentException If the passsword date is not a valid DateTime.
     * @return self
     */
    public function setLastPasswordDate($lastPasswordDate)
    {
        if ($lastPasswordDate === null) {
            $this->lastPasswordDate = null;
            return $this;
        }

        if (is_string($lastPasswordDate)) {
            try {
                $lastPasswordDate = new DateTime($lastPasswordDate);
            } catch (Exception $e) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid last password date (%s)',
                    $e->getMessage()
                ), 0, $e);
            }
        }

        if (!($lastPasswordDate instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Last Password Date" value. Must be a date/time string or a DateTime object.'
            );
        }

        $this->lastPasswordDate = $lastPasswordDate;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getLastPasswordDate()
    {
        return $this->lastPasswordDate;
    }

    /**
     * @param  integer|string|null $ip The last password IP.
     * @throws InvalidArgumentException If the IP is not null, an integer or an IP string.
     * @return self
     */
    public function setLastPasswordIp($ip)
    {
        if ($ip === null) {
            $this->lastPasswordIp = null;
            return $this;
        }

        if (is_int($ip)) {
            $ip = long2ip($ip);
        }

        if (!is_string($ip)) {
            throw new InvalidArgumentException(
                'Invalid IP address'
            );
        }

        $this->lastPasswordIp = $ip;

        return $this;
    }

    /**
     * Get the last password change IP in x.x.x.x format
     *
     * @return string|null
     */
    public function getLastPasswordIp()
    {
        return $this->lastPasswordIp;
    }

    /**
     * @param  string|null $token The login token.
     * @throws InvalidArgumentException If the token is not a string.
     * @return self
     */
    public function setLoginToken($token)
    {
        if ($token === null) {
            $this->loginToken = null;
            return $this;
        }

        if (!is_string($token)) {
            throw new InvalidArgumentException(
                'Login Token must be a string'
            );
        }

        $this->loginToken = $token;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLoginToken()
    {
        return $this->loginToken;
    }

    /**
     * @param  mixed $preferences Structure of user preferences.
     * @return self
     */
    public function setPreferences($preferences)
    {
        $this->preferences = $preferences;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPreferences()
    {
        return $this->preferences;
    }



    // Extends Charcoal\User\Access\AuthenticatableTrait
    // =========================================================================

    /**
     * Retrieve the name of the unique ID for the user.
     *
     * @return string
     */
    public function getAuthIdKey()
    {
        return $this->key();
    }

    /**
     * Retrieve the name of the login username for the user.
     *
     * @return string
     */
    public function getAuthIdentifierKey()
    {
        return 'email';
    }

    /**
     * Retrieve the name of the login password for the user.
     *
     * @return string
     */
    public function getAuthPasswordKey()
    {
        return 'password';
    }

    /**
     * Retrieve the name of the login token for the user.
     *
     * @return string
     */
    public function getAuthLoginTokenKey()
    {
        return 'login_token';
    }


    // Extends Charcoal\Validator\ValidatableTrait
    // =========================================================================

    /**
     * Validate the user model.
     *
     * @param  ValidatorInterface $v Optional. A custom validator object to use for validation. If null, use object's.
     * @return boolean
     */
    public function validate(ValidatorInterface &$v = null)
    {
        $result = parent::validate($v);

        if (!$this->validateLoginRequired()) {
            return false;
        }

        if (!$this->validateLoginUnique()) {
            return false;
        }

        return $result;
    }

    /**
     * Validate the username or email address.
     *
     * @return boolean
     */
    protected function validateLoginRequired()
    {
        $userKey   = $this->getAuthIdentifierKey();
        $userLogin = $this->getAuthIdentifier();

        if (empty($userLogin)) {
            $this->validator()->error(
                sprintf('User Credentials: "%s" is required.', $userKey),
                $userKey
            );
            return false;
        }

        if (strpos($userKey, 'email') !== false && !filter_var($userLogin, FILTER_VALIDATE_EMAIL)) {
            $this->validator()->error(
                'User Credentials: Email format is incorrect.',
                $userKey
            );
            return false;
        }

        return true;
    }

    /**
     * Validate the username or email address is unique.
     *
     * @return boolean
     */
    protected function validateLoginUnique()
    {
        $userKey   = $this->getAuthIdentifierKey();
        $userLogin = $this->getAuthIdentifier();

        $objType = self::objType();
        $factory = $this->modelFactory();

        $originalUser = $factory->create($objType)->load($this->getAuthId());

        if (mb_strtolower($originalUser->getAuthIdentifier()) !== mb_strtolower($userLogin)) {
            $existingUser = $factory->create($objType)->loadFrom($userKey, $userLogin);
            /** Check for existing user with given email. */
            if (!empty($existingUser->getAuthId())) {
                $this->validator()->error(
                    sprintf('User Credentials: "%s" is not available.', $userKey),
                    $userKey
                );

                return false;
            }
        }

        return true;
    }
}
