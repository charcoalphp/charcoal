<?php

namespace Charcoal\User;

use InvalidArgumentException;
use RuntimeException;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-user'
use Charcoal\User\Access\AuthenticatableInterface;

/**
 * The Authenticator service helps with user authentication / login.
 *
 * ## Constructor dependencies
 *
 * Constructor dependencies are passed as an array of `key=>value` pair.
 * The required dependencies are:
 *
 * - `logger` A PSR3 logger instance
 * - `user_type` The user object type (FQN or ident)
 * - `user_factory` The Factory used to instanciate new users.
 * - `token_type` The auth token object type (FQN or ident)
 * - `token_factory` The Factory used to instanciate new auth tokens.
 */
class Authenticator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const METHOD_PASSWORD = 'password';
    const METHOD_SESSION  = 'session';
    const METHOD_TOKEN    = 'token';

    /**
     * The authenticated user.
     *
     * @var \Charcoal\User\UserInterface
     */
    private $authenticatedUser;

    /**
     * The authentication method.
     *
     * @var string
     */
    private $authenticatedMethod;

    /**
     * Indicate if the a user is authenticated.
     *
     * @var boolean
     */
    private $isAuthenticated;

    /**
     * The user object type.
     *
     * @var string
     */
    private $userType;

    /**
     * Store the user model factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $userFactory;

    /**
     * The auth-token object type.
     *
     * @var string
     */
    private $tokenType;

    /**
     * Store the auth-token model factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $tokenFactory;

    /**
     * @param array $data Class dependencies.
     */
    public function __construct(array $data)
    {
        $this->setLogger($data['logger']);
        $this->setUserType($data['user_type']);
        $this->setUserFactory($data['user_factory']);
        $this->setTokenType($data['token_type']);
        $this->setTokenFactory($data['token_factory']);
    }

    /**
     * Retrieve the user object type.
     *
     * @return string
     */
    protected function userType()
    {
        return $this->userType;
    }

    /**
     * Retrieve the user model factory.
     *
     * @throws RuntimeException If the model factory was not previously set.
     * @return FactoryInterface
     */
    protected function userFactory()
    {
        return $this->userFactory;
    }

    /**
     * Retrieve the auth-token object type.
     *
     * @return string
     */
    protected function tokenType()
    {
        return $this->tokenType;
    }

    /**
     * Retrieve the auth-token model factory.
     *
     * @throws RuntimeException If the token factory was not previously set.
     * @return FactoryInterface
     */
    protected function tokenFactory()
    {
        return $this->tokenFactory;
    }

    /**
     * Set the user object type (model).
     *
     * @param string $type The user object type.
     * @throws InvalidArgumentException If the user object type parameter is not a string.
     * @return void
     */
    private function setUserType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'User object type must be a string'
            );
        }

        $this->userType = $type;
    }

    /**
     * Set a user model factory.
     *
     * @param FactoryInterface $factory The factory used to create new user instances.
     * @return void
     */
    private function setUserFactory(FactoryInterface $factory)
    {
        $this->userFactory = $factory;
    }

    /**
     * Set the authorization token type (model).
     *
     * @param string $type The auth-token object type.
     * @throws InvalidArgumentException If the token object type parameter is not a string.
     * @return void
     */
    private function setTokenType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Token object type must be a string'
            );
        }

        $this->tokenType = $type;
    }

    /**
     * Set a model factory for token-based authentication.
     *
     * @param FactoryInterface $factory The factory used to create new auth-token instances.
     * @return void
     */
    private function setTokenFactory(FactoryInterface $factory)
    {
        $this->tokenFactory = $factory;
    }

    /**
     * Retrieve the currently authenticated user.
     *
     * The method will attempt to authenticate a
     *
     * @return \Charcoal\User\UserInterface|null
     */
    public function user()
    {
        if ($this->isAuthenticated === null) {
            $this->authenticate();
        }

        return $this->authenticatedUser;
    }

    /**
     * Return the currently cached user.
     *
     * @return \Charcoal\User\UserInterface|null
     */
    public function getUser()
    {
        return $this->authenticatedUser;
    }

    /**
     * Set the authenticated user.
     *
     * @param  AuthenticatableInterface $user The authenticated user.
     * @return void
     */
    protected function setUser(AuthenticatableInterface $user)
    {
        $this->authenticatedUser = $user;
        $this->isAuthenticated   = true;
    }

    /**
     * Determines if the user is authenticated or not.
     *
     * @return bool TRUE if a user has been authenticated, FALSE otherwise.
     */
    public function isAuthenticated()
    {
        if ($this->isAuthenticated === null) {
            $this->authenticate();
        }

        return $this->isAuthenticated;
    }

    /**
     * Log a user into the application.
     *
     * @param  AuthenticatableInterface $user     The authenticated user to log in.
     * @param  boolean                  $remember Whether to "remember" the user or not.
     * @return boolean Success / Failure
     */
    public function login(AuthenticatableInterface $user, $remember = false)
    {
        if (!$user->getAuthId()) {
            return false;
        }

        $this->updateSession($user);

        if ($remember) {
            $this->updateCookie($user);
        }

        $this->touchUserLogin($user);

        $this->setUser($user);

        return true;
    }

    /**
     * Log the user out of the application.
     *
     * @return boolean Logged out or not.
     */
    public function logout()
    {
        $user = $this->user();

        if ($user === null) {
            $user = $this->userFactory()->get($this->userType());
        }

        $key = $user::sessionKey();

        $_SESSION[$key] = null;
        unset($_SESSION[$key]);

        $this->authenticatedMethod = null;
        $this->authenticatedUser   = null;
        $this->isAuthenticated     = false;

        return true;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * The user is authenticated via _session ID_ or _auth token_.
     *
     * @return \Charcoal\User\UserInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    public function authenticate()
    {
        $user = $this->authenticateBySession();
        if ($user) {
            return $user;
        }

        $user = $this->authenticateByToken();
        if ($user) {
            return $user;
        }

        $this->authenticatedMethod = null;
        $this->authenticatedUser   = null;
        $this->isAuthenticated     = false;

        return null;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  string $identifier The login ID, part of necessary credentials.
     * @param  string $password   The password, part of necessary credentials.
     * @throws InvalidArgumentException If the credentials are invalid or missing.
     * @return \Charcoal\User\UserInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    public function authenticateByPassword($identifier, $password)
    {
        if ($this->validateLogin($identifier, $password)) {
            throw new InvalidArgumentException(
                'Invalid credentials'
            );
        }

        $user = $this->userFactory()->create($this->userType());
        if (!$user->source()->tableExists()) {
            $user->source()->createTable();
        }

        // Load the user by email
        $user->loadFrom($user->getAuthIdentifierKey(), $identifier);

        // Check identifier is as requested
        if ($user->getAuthIdentifier() !== $identifier) {
            return null;
        }

        // Allow model to validate user standing
        if (!$this->validateAuthentication($user)) {
            return null;
        }

        // Validate password
        $hashedPassword = $user->getAuthPassword();
        if (password_verify($password, $hashedPassword)) {
            if (password_needs_rehash($hashedPassword, PASSWORD_DEFAULT)) {
                $this->rehashUserPassword($user, $password);
            }

            $this->login($user);
            $this->authenticatedMethod = static::METHOD_PASSWORD;

            return $user;
        }

        $this->logger->warning(sprintf(
            'Invalid login attempt for user "%s": invalid password.',
             $identifier
        ));

        return null;
    }

    /**
     * Attempt to authenticate a user using their session ID.
     *
     * @return \Charcoal\User\UserInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    private function authenticateBySession()
    {
        $user = $this->userFactory()->create($this->userType());
        $key  = $user::sessionKey();

        if (!isset($_SESSION[$key])) {
            return null;
        }

        $userId = $_SESSION[$key];
        if (!$userId) {
            return null;
        }

        $user->load($userId);

        // Allow model to validate user standing
        if (!$this->validateAuthentication($user)) {
            return null;
        }

        $this->updateSession($user);

        $this->setUser($user);
        $this->authenticatedMethod = static::METHOD_SESSION;

        return $user;
    }

    /**
     * Attempt to authenticate a user using their auth token.
     *
     * @return \Charcoal\User\UserInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    private function authenticateByToken()
    {
        $authToken = $this->tokenFactory()->create($this->tokenType());

        if ($authToken->metadata()['enabled'] !== true) {
            return null;
        }

        $tokenData = $authToken->getTokenDataFromCookie();
        if (!$tokenData) {
            return null;
        }

        $userId = $authToken->getUserIdFromToken($tokenData['ident'], $tokenData['token']);
        if (!$userId) {
            return null;
        }

        $user = $this->userFactory()->create($this->userType());
        $user->load($userId);

        // Allow model to validate user standing
        if (!$this->validateAuthentication($user)) {
            return null;
        }

        $this->updateSession($user);

        $this->setUser($user);
        $this->authenticatedMethod = static::METHOD_TOKEN;

        return $user;
    }

    /**
     * Update the session with the given user.
     *
     * @param  AuthenticatableInterface $user The authenticated user to remember.
     * @throws InvalidArgumentException If trying to save a user to session without an ID.
     * @return void
     */
    protected function updateSession(AuthenticatableInterface $user)
    {
        $userId = $user->getAuthId();
        if (!$userId) {
            throw new InvalidArgumentException(
                'Can not save user to session; no user ID'
            );
        }

        $_SESSION[$user::sessionKey()] = $userId;
    }

    /**
     * Store the auth token for the given user in a cookie.
     *
     * @param  AuthenticatableInterface $user The authenticated user to remember.
     * @throws InvalidArgumentException If trying to save a user to cookies without an ID.
     * @return void
     */
    protected function updateCookie(AuthenticatableInterface $user)
    {
        $userId = $user->getAuthId();
        if (!$userId) {
            throw new InvalidArgumentException(
                'Can not save user to session; no user ID'
            );
        }

        $authToken = $this->tokenFactory()->create($this->tokenType());
        $authToken->generate($userId);
        $authToken->sendCookie();

        $authToken->save();
    }

    /**
     * Validate the user login credentials are acceptable.
     *
     * @param  string $identifier The user identifier to check.
     * @param  string $password   The user password to check.
     * @return boolean Returns TRUE if the credentials are acceptable, or FALSE otherwise.
     */
    public function validateLogin($identifier, $password)
    {
        return ($this->validateAuthIdentifier($identifier) && $this->validateAuthPassword($password));
    }

    /**
     * Validate the user identifier is acceptable.
     *
     * @param  string $identifier The login ID.
     * @return boolean Returns TRUE if the identifier is acceptable, or FALSE otherwise.
     */
    public function validateAuthIdentifier($identifier)
    {
        return (is_string($identifier) && !empty($identifier));
    }

    /**
     * Validate the user password is acceptable.
     *
     * @param  string $password The password.
     * @return boolean Returns TRUE if the password is acceptable, or FALSE otherwise.
     */
    public function validateAuthPassword($password)
    {
        return (is_string($password) && !empty($password));
    }

    /**
     * Validate the user authentication state is okay.
     *
     * For example, inactive users can not authenticate.
     *
     * @param  AuthenticatableInterface $user The user to validate.
     * @return boolean
     */
    public function validateAuthentication(AuthenticatableInterface $user)
    {
        return !!((!$user->getAuthId() || !$user->getAuthIdentifier() || !$user['active']));
    }

    /**
     * Updates the user's timestamp for their last log in.
     *
     * @param  AuthenticatableInterface $user     The user to update.
     * @param  string                   $password The plain-text password to hash.
     * @return boolean Returns TRUE if the password was changed, or FALSE otherwise.
     */
    protected function touchUserLogin(AuthenticatableInterface $user)
    {
        if (!$user->getAuthId()) {
            throw new InvalidArgumentException(
                'Can not touch user: user has no ID'
            );
        }

        $userIdent = $user->getAuthIdentifier();
        $userClass = get_class($user);

        $this->logger->info(sprintf(
            'Updating last login fields for user "%s" (%s)',
            $userIdent,
            $userClass
        ));

        $user['lastLoginDate'] = 'now';
        $user['lastLoginIp']   = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;

        $result = $user->update([
            'last_login_ip',
            'last_login_date',
        ]);

        if ($result) {
            $this->logger->notice(sprintf(
                'Last login fields were updated for user "%s" (%s)',
                $userIdent,
                $userClass
            ));
        } else {
            $this->logger->warning(sprintf(
                'Last login fields failed to be updated for user "%s" (%s)',
                $userIdent,
                $userClass
            ));
        }

        return $result;
    }

    /**
     * Updates the user's password hash.
     *
     * Assumes that the existing hash needs to be rehashed.
     *
     * @param  AuthenticatableInterface $user     The user to update.
     * @param  string                   $password The plain-text password to hash.
     * @return boolean Returns TRUE if the password was changed, or FALSE otherwise.
     */
    protected function rehashUserPassword(AuthenticatableInterface $user, $password)
    {
        if (!$this->validateAuthPassword($password)) {
            throw new InvalidArgumentException(
                'Can not rehash password: password is invalid'
            );
        }

        if (!$user->getAuthId()) {
            throw new InvalidArgumentException(
                'Can not rehash password: user has no ID'
            );
        }

        $userIdent = $user->getAuthIdentifier();
        $userClass = get_class($user);

        $this->logger->info(sprintf(
            'Rehashing password for user "%s" (%s)',
            $userIdent,
            $userClass
        ));

        $passwordKey = $user->getAuthPasswordKey();

        $user[$passwordKey] = password_hash($password, PASSWORD_DEFAULT);
        $result = $user->update([
            $passwordKey,
        ]);

        if ($result) {
            $this->logger->notice(sprintf(
                'Password was rehashed for user "%s" (%s)',
                $userIdent,
                $userClass
            ));
        } else {
            $this->logger->warning(sprintf(
                'Password failed to be rehashed for user "%s" (%s)',
                $userIdent,
                $userClass
            ));
        }

        return $result;
    }

    /**
     * Updates the user's password hash.
     *
     * @param  AuthenticatableInterface $user     The user to update.
     * @param  string                   $password The plain-text password to hash.
     * @return boolean Returns TRUE if the password was changed, or FALSE otherwise.
     */
    protected function changeUserPassword(AuthenticatableInterface $user, $password)
    {
        if (!$this->validateAuthPassword($password)) {
            throw new InvalidArgumentException(
                'Can not reset password: password is invalid'
            );
        }

        if (!$user->getAuthId()) {
            throw new InvalidArgumentException(
                'Can not reset password: user has no ID'
            );
        }

        $userIdent = $user->getAuthIdentifier();
        $userClass = get_class($user);

        $this->logger->info(sprintf(
            'Changing password for user "%s" (%s)',
            $userIdent,
            $userClass
        ));

        $passwordKey = $user->getAuthPasswordKey();

        $user[$passwordKey]       = password_hash($password, PASSWORD_DEFAULT);
        $user['lastPasswordDate'] = 'now';
        $user['lastPasswordIp']   = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;

        $result = $user->update([
            $passwordKey,
            'last_password_date',
            'last_password_ip',
        ]);

        if ($result) {
            $this->logger->notice(sprintf(
                'Password was changed for user "%s" (%s)',
                $userIdent,
                $userClass
            ));
        } else {
            $this->logger->warning(sprintf(
                'Password failed to be changed for user "%s" (%s)',
                $userIdent,
                $userClass
            ));
        }

        return $result;
    }
}
