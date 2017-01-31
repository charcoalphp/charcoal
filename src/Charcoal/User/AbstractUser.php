<?php

namespace Charcoal\User;

// Dependencies from `PHP`
use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

// Module `charcoal-factory` dependencies
use Charcoal\Factory\FactoryInterface;

// Module `charcoal-config` dependencies
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;

// Module `charcoal-base` dependencies
use Charcoal\Object\Content;

// Local namespace (charcoal-base) dependencies
use Charcoal\User\UserInterface;

/**
 * Full implementation, as abstract class, of the `UserInterface`.
 */
abstract class AbstractUser extends Content implements
    UserInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;

    /**
     * @var UserInterface $authenticatedUser
     */
    protected static $authenticatedUser;

    /**
     * The username should be unique and mandatory.
     * It is also used as login name and main identifier (key).
     *
     * @var string
     */
    private $username = '';

    /**
     * The password is stored encrypted in the (database) storage.
     * @var string $password
     */
    private $password;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string[]
     */
    private $roles = [];

    /**
     * The date of the latest (successful) login
     * @var DateTimeInterface|null
     */
    private $lastLoginDate;

    /**
     * @var string|null
     */
    private $lastLoginIp;

    /**
     * The date of the latest password change
     * @var DateTimeInterface|null
     */
    private $lastPasswordDate;

    /**
     * @var string|null $lastPasswordIp
     */
    private $lastPasswordIp;

    /**
     * If the login token is set (not empty), then the user should be prompted to
     * reset his password after login / enter the token to continue
     * @var string|null $loginToken
     */
    private $loginToken = '';

    /**
     * IndexableTrait > key()
     *
     * @return string
     */
    public function key()
    {
        return 'username';
    }

    /**
     * Force a lowercase username
     *
     * @param string $username The username (also the login name).
     * @throws InvalidArgumentException If the username is not a string.
     * @return UserInterface Chainable
     */
    public function setUsername($username)
    {
        if (!is_string($username)) {
            throw new InvalidArgumentException(
                'Set user username: Username must be a string'
            );
        }
        $this->username = mb_strtolower($username);
        return $this;
    }

    /**
     * @return string
     */
    public function username()
    {
        return $this->username;
    }

    /**
     * @param string $email The user email.
     * @throws InvalidArgumentException If the email is not a string.
     * @return UserInterface Chainable
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
    public function email()
    {
        return $this->email;
    }

    /**
     * @param string|null $password The user password. Encrypted in storage.
     * @throws InvalidArgumentException If the password is not a string (or null, to reset).
     * @return UserInterface Chainable
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
     * @return string
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * @param string|string[]|null $roles The ACL roles this user belongs to.
     * @throws InvalidArgumentException If the roles argument is invalid.
     * @return UserInterface Chainable
     */
    public function setRoles($roles)
    {
        if ($roles === null) {
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
        $this->roles = array_map('trim', $roles);
        return $this;
    }

    /**
     * @return string[]
     */
    public function roles()
    {
        return $this->roles;
    }

    /**
     * @param string|DateTimeInterface|null $lastLoginDate The last login date.
     * @throws InvalidArgumentException If the ts is not a valid date/time.
     * @return UserInterface Chainable
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
                throw new InvalidArgumentException(
                    sprintf('Invalid login date (%s)', $e->getMessage())
                );
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
    public function lastLoginDate()
    {
        return $this->lastLoginDate;
    }

    /**
     * @param string|integer|null $ip The last login IP address.
     * @throws InvalidArgumentException If the IP is not an IP string, an integer, or null.
     * @return UserInterface Chainable
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
     * @return string
     */
    public function lastLoginIp()
    {
        return $this->lastLoginIp;
    }

    /**
     * @param string|DateTimeInterface|null $lastPasswordDate The last password date.
     * @throws InvalidArgumentException If the passsword date is not a valid DateTime.
     * @return UserInterface Chainable
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
                throw new InvalidArgumentException(
                    sprintf('Invalid last password date (%s)', $e->getMessage())
                );
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
    public function lastPasswordDate()
    {
        return $this->lastPasswordDate;
    }

    /**
     * @param integer|string|null $ip The last password IP.
     * @throws InvalidArgumentException If the IP is not null, an integer or an IP string.
     * @return UserInterface Chainable
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
     * @return string
     */
    public function lastPasswordIp()
    {
        return $this->lastPasswordIp;
    }

    /**
     * @param string $token The login token.
     * @throws InvalidArgumentException If the token is not a string.
     * @return UserInterface Chainable
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
     * @return string
     */
    public function loginToken()
    {
        return $this->loginToken;
    }

    /**
     * @throws Exception If trying to save a user to session without a ID.
     * @return UserInterface Chainable
     */
    public function saveToSession()
    {
        if (!$this->id()) {
            throw new Exception(
                'Can not set auth user; no user ID'
            );
        }
        $_SESSION[static::sessionKey()] = $this->id();
        return $this;
    }

    /**
     * Log in the user (in session)
     *
     * Called when the authentication is successful.
     *
     * @return boolean Success / Failure
     */
    public function login()
    {
        if (!$this->id()) {
            return false;
        }

        $this->setLastLoginDate('now');
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        if ($ip) {
            $this->setLastLoginIp($ip);
        }
        $this->update(['last_login_ip', 'last_login_date']);

        $this->saveToSession();

        return true;
    }

    /**
     * @return boolean
     */
    public function logLogin()
    {
        // @todo
        return true;
    }

    /**
     * Failed authentication callback
     *
     * @param string $username The failed username.
     * @return void
     */
    public function loginFailed($username)
    {
        $this->setUsername('');

        $this->logLoginFailed($username);
    }

    /**
     * @param string $username The username to log failure.
     * @return boolean
     */
    public function logLoginFailed($username)
    {
        // @todo
        return true;
    }

    /**
     * Empties the session var associated to the session key.
     *
     * @return boolean Logged out or not.
     */
    public function logout()
    {
        // Irrelevant call...
        if (!$this->id()) {
            return false;
        }

        $_SESSION[static::sessionKey()] = null;
        unset($_SESSION[static::sessionKey()]);

        return true;
    }

    /**
     * Reset the password.
     *
     * Encrypt the password and re-save the object in the database.
     * Also updates the last password date & ip.
     *
     * @param string $plainPassword The plain (non-encrypted) password to reset to.
     * @throws InvalidArgumentException If the plain password is not a string.
     * @return UserInterface Chainable
     */
    public function resetPassword($plainPassword)
    {
        if (!is_string($plainPassword)) {
            throw new InvalidArgumentException(
                'Can not change password: password is not a string.'
            );
        }

        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $this->setPassword($hash);

        $this->setLastPasswordDate('now');
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        if ($ip) {
            $this->setLastPasswordIp($ip);
        }

        if ($this->id()) {
            $this->update(['password', 'last_password_date', 'last_password_ip']);
        }

        return $this;
    }

    /**
     * Get the currently authenticated user (from session)
     *
     * Return null if there is no current user in logged into
     *
     * @param FactoryInterface $factory The factory to create the user object with.
     * @throws Exception If the user from session is invalid.
     * @return UserInterface|null
     */
    public static function getAuthenticated(FactoryInterface $factory)
    {
        if (isset(static::$authenticatedUser[static::sessionKey()])) {
            return static::$authenticatedUser[static::sessionKey()];
        }

        if (!isset($_SESSION[static::sessionKey()])) {
            return null;
        }

        $userId = $_SESSION[static::sessionKey()];
        if (!$userId) {
            return null;
        }

        $userClass = get_called_class();
        $user = $factory->create($userClass);
        $user->load($userId);

        // Inactive users can not authenticate
        if (!$user->id() || !$user->username() || !$user->active()) {
            return null;
        }

        static::$authenticatedUser[static::sessionKey()] = $user;
        return $user;
    }
}
