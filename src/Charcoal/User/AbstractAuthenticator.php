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
 * The base Authenticator service
 *
 * Helps with user authentication / login.
 *
 * ## Constructor dependencies
 *
 * Constructor dependencies are passed as an array of `key => value` pair.
 * The required dependencies are:
 *
 * - `logger` A PSR3 logger instance
 * - `user_type` The user object type (FQN or ident)
 * - `user_factory` The Factory used to instanciate new users.
 * - `token_type` The auth token object type (FQN or ident)
 * - `token_factory` The Factory used to instanciate new auth tokens.
 */
abstract class AbstractAuthenticator implements
    AuthenticatorInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    const AUTH_BY_PASSWORD = 'password';
    const AUTH_BY_SESSION  = 'session';
    const AUTH_BY_TOKEN    = 'token';

    /**
     * The user that was last authenticated.
     *
     * @var AuthenticatableInterface|null
     */
    protected $authenticatedUser;

    /**
     * The token that was last authenticated.
     *
     * @var \Charcoal\User\AuthTokenInterface|null
     */
    protected $authenticatedToken;

    /**
     * The authentication method of the user that was last authenticated.
     *
     * @var string|null
     */
    protected $authenticatedMethod;

    /**
     * Indicates if the logout method has been called.
     *
     * @var boolean
     */
    protected $isLoggedOut = false;

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
     * @param array $data Authenticator dependencies.
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
    public function userType()
    {
        return $this->userType;
    }

    /**
     * Retrieve the user model factory.
     *
     * @throws RuntimeException If the model factory was not previously set.
     * @return FactoryInterface
     */
    public function userFactory()
    {
        return $this->userFactory;
    }

    /**
     * Create a new user model.
     *
     * @return \Charcoal\User\Access\AuthenticatableInterface
     */
    public function createUser()
    {
        return $this->userFactory()->create($this->userType());
    }

    /**
     * Retrieve the auth-token object type.
     *
     * @return string
     */
    public function tokenType()
    {
        return $this->tokenType;
    }

    /**
     * Retrieve the auth-token model factory.
     *
     * @throws RuntimeException If the token factory was not previously set.
     * @return FactoryInterface
     */
    public function tokenFactory()
    {
        return $this->tokenFactory;
    }

    /**
     * Create a new auth-token model.
     *
     * @return \Charcoal\User\AuthTokenInterface
     */
    public function createToken()
    {
        return $this->tokenFactory()->create($this->tokenType());
    }

    /**
     * Set the user object type (model).
     *
     * @param  string $type The user object type.
     * @throws InvalidArgumentException If the user object type parameter is not a string.
     * @return void
     */
    protected function setUserType($type)
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
     * @param  FactoryInterface $factory The factory used to create new user instances.
     * @return void
     */
    protected function setUserFactory(FactoryInterface $factory)
    {
        $this->userFactory = $factory;
    }

    /**
     * Set the authorization token type (model).
     *
     * @param  string $type The auth-token object type.
     * @throws InvalidArgumentException If the token object type parameter is not a string.
     * @return void
     */
    protected function setTokenType($type)
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
     * @param  FactoryInterface $factory The factory used to create new auth-token instances.
     * @return void
     */
    protected function setTokenFactory(FactoryInterface $factory)
    {
        $this->tokenFactory = $factory;
    }

    /**
     * Retrieve the currently authenticated user.
     *
     * The method will attempt to authenticate a user.
     *
     * @return AuthenticatableInterface|null
     */
    public function user()
    {
        if ($this->isLoggedOut()) {
            return null;
        }

        if ($this->authenticatedUser === null) {
            $this->authenticate();
        }

        return $this->authenticatedUser;
    }

    /**
     * Retrieve the ID for the currently authenticated user.
     *
     * The method will attempt to authenticate a user.
     *
     * @return mixed
     */
    public function userId()
    {
        if ($this->isLoggedOut()) {
            return null;
        }

        $user = $this->user();
        if (!$user) {
            return null;
        }

        return $user->getAuthId();
    }

    /**
     * Retrieve the currently cached user.
     *
     * @return AuthenticatableInterface|null
     */
    public function getUser()
    {
        return $this->authenticatedUser;
    }

    /**
     * Retrieve the ID for the currently cached user.
     *
     * @return mixed
     */
    public function getUserId()
    {
        $user = $this->authenticatedUser;
        if (!$user) {
            return null;
        }

        return $user->getAuthId();
    }

    /**
     * Set the authenticated user.
     *
     * Log a user into the application without sessions or cookies.
     *
     * @param  AuthenticatableInterface $user The authenticated user.
     * @return void
     */
    public function setUser(AuthenticatableInterface $user)
    {
        $this->authenticatedUser = $user;
        $this->isLoggedOut       = false;
    }

    /**
     * Set the authenticated user from the given user ID.
     *
     * Log a user into the application without sessions or cookies.
     *
     * @param  mixed $userId The authenticated user ID.
     * @return void
     */
    public function setUserById($userId)
    {
        $user = $this->createUser();
        $user->loadFrom($user->getAuthIdKey(), $userId);

        // Allow model to validate user standing
        if ($this->validateAuthentication($user)) {
            $this->setUser($user);
        }
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return boolean
     */
    public function check()
    {
        return $this->user() !== null;
    }

    /**
     * Determines if the logout method has been called.
     *
     * @return boolean TRUE if the logout method has been called, FALSE otherwise.
     */
    protected function isLoggedOut()
    {
        return $this->isLoggedOut;
    }

    /**
     * Retrieve the authentication method of the current user.
     *
     * If the current user is authenticated, one of the
     * `self::AUTH_BY_*` constants is returned.
     *
     * @return string|null
     */
    public function getAuthenticationMethod()
    {
        return $this->authenticatedMethod;
    }

    /**
     * Retrieve the authentication token of the current user.
     *
     * If the current user was authenticated by token,
     * the auth token instance is returned.
     *
     * @return AuthTokenInterface|null
     */
    public function getAuthenticationToken()
    {
        return $this->authenticatedToken;
    }

    /**
     * Log a user into the application.
     *
     * @param  AuthenticatableInterface $user     The authenticated user to log in.
     * @param  boolean                  $remember Whether to "remember" the user or not.
     * @return void
     */
    public function login(AuthenticatableInterface $user, $remember = false)
    {
        if (!$user->getAuthId()) {
            return;
        }

        $this->updateUserSession($user);

        if ($remember) {
            $this->updateCurrentToken($user);
        }

        $this->setUser($user);
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->user();

        $this->deleteUserSession($user);
        $this->deleteUserTokens($user);

        $this->clearAuthenticator();
    }

    /**
     * Attempt to authenticate a user by session or token.
     *
     * The user is authenticated via _session ID_ or _auth token_.
     *
     * @return AuthenticatableInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    public function authenticate()
    {
        if ($this->isLoggedOut()) {
            return null;
        }

        $user = $this->authenticateBySession();
        if ($user) {
            return $user;
        }

        $user = $this->authenticateByToken();
        if ($user) {
            return $user;
        }

        $this->authenticatedMethod = null;
        $this->authenticatedToken  = null;
        $this->authenticatedUser   = null;

        return null;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  string  $identifier The login ID, part of necessary credentials.
     * @param  string  $password   The password, part of necessary credentials.
     * @param  boolean $remember   Whether to "remember" the user or not.
     * @throws InvalidArgumentException If the credentials are invalid or missing.
     * @return AuthenticatableInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    public function authenticateByPassword($identifier, $password, $remember = false)
    {
        if (!$this->validateLogin($identifier, $password)) {
            throw new InvalidArgumentException(
                'Invalid user login credentials'
            );
        }

        $user = $this->createUser();
        if (!$user->source()->tableExists()) {
            $this->logger->warning(sprintf(
                '[Authenticator] Invalid login attempt for user "%s" (%s): The table "%s" does not exist.',
                $identifier,
                get_class($user),
                $user->source()->table()
            ));
            return null;
        }

        // Load the user by email
        $user->loadFrom($user->getAuthIdentifierKey(), $identifier);

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

            $this->login($user, $remember);
            $this->authenticatedMethod = static::AUTH_BY_PASSWORD;

            return $user;
        }

        $this->logger->warning(sprintf(
            '[Authenticator] Invalid login attempt for user "%s" (%s): invalid password.',
            $identifier,
            get_class($user)
        ));

        return null;
    }

    /**
     * Attempt to authenticate a user using their session ID.
     *
     * @return AuthenticatableInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    protected function authenticateBySession()
    {
        $user = $this->createUser();
        if (!$user->source()->tableExists()) {
            $this->logger->warning(sprintf(
                '[Authenticator] Invalid login attempt by session for a user (%s): The table "%s" does not exist.',
                get_class($user),
                $user->source()->table()
            ));
            return null;
        }

        $key = $user::sessionKey();
        if (empty($key) || !isset($_SESSION[$key])) {
            return null;
        }

        $userId = $_SESSION[$key];
        if (!$userId) {
            return null;
        }

        $user->loadFrom($user->getAuthIdKey(), $userId);

        // Allow model to validate user standing
        if (!$this->validateAuthentication($user)) {
            return null;
        }

        $this->updateUserSession($user);

        $this->setUser($user);
        $this->authenticatedMethod = static::AUTH_BY_SESSION;

        return $user;
    }

    /**
     * Attempt to authenticate a user using their auth token.
     *
     * @return AuthenticatableInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    protected function authenticateByToken()
    {
        $authToken = $this->createToken();

        if (!$authToken->isEnabled()) {
            return null;
        }

        if (!($authToken instanceof AuthTokenCookieInterface)) {
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

        $user = $this->createUser();
        if (!$user->source()->tableExists()) {
            $this->logger->warning(sprintf(
                '[Authenticator] Invalid login attempt by token for a user (%s): The table "%s" does not exist.',
                get_class($user),
                $user->source()->table()
            ));
            return null;
        }

        $user->loadFrom($user->getAuthIdKey(), $userId);

        // Allow model to validate user standing
        if (!$this->validateAuthentication($user)) {
            return null;
        }

        $this->updateUserSession($user);

        $this->setUser($user);
        $this->authenticatedMethod = static::AUTH_BY_TOKEN;
        $this->authenticatedToken  = $authToken;

        return $user;
    }

    /**
     * Delete the user data from the session.
     *
     * @param  AuthenticatableInterface|null $user The authenticated user to forget.
     * @return void
     */
    protected function deleteUserSession(AuthenticatableInterface $user = null)
    {
        if ($user === null) {
            $user = $this->userFactory()->get($this->userType());
        }

        $key = $user::sessionKey();

        $_SESSION[$key] = null;
        unset($_SESSION[$key]);
    }

    /**
     * Delete the user data from the cookie.
     *
     * @param  AuthenticatableInterface|null $user The authenticated user to forget.
     * @return void
     */
    protected function deleteUserTokens(AuthenticatableInterface $user = null)
    {
        $authToken = $this->createToken();
        if (!$authToken->isEnabled()) {
            return;
        }

        $authToken->deleteCookie();

        if ($user === null) {
            return;
        }

        $userId = $user->getAuthId();
        if (!$userId) {
            return;
        }

        $authToken['userId'] = $userId;
        $authToken->deleteUserAuthTokens();
    }

    /**
     * Delete the user data from the cookie.
     *
     * @throws InvalidArgumentException If trying to save a user to cookies without an ID.
     * @return void
     */
    protected function deleteCurrentToken()
    {
        $authToken = $this->authenticatedToken;
        if ($authToken === null) {
            return;
        }

        $this->authenticatedToken = null;

        if (!$authToken->isEnabled()) {
            return;
        }

        $authToken->deleteCookie();
        $authToken->delete();
    }

    /**
     * Update the session with the given user.
     *
     * @param  AuthenticatableInterface $user The authenticated user to remember.
     * @throws InvalidArgumentException If trying to save a user to session without an ID.
     * @return void
     */
    protected function updateUserSession(AuthenticatableInterface $user)
    {
        $userId = $user->getAuthId();
        if (!$userId) {
            throw new InvalidArgumentException(
                'Can not save user data to session; no user ID'
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
    protected function updateCurrentToken(AuthenticatableInterface $user)
    {
        $userId = $user->getAuthId();
        if (!$userId) {
            throw new InvalidArgumentException(
                'Can not save user data to cookie; no user ID'
            );
        }

        $authToken = $this->createToken();

        if (!$authToken->isEnabled()) {
            return;
        }

        $authToken->generate($userId);
        $authToken->sendCookie();
        $authToken->save();

        $this->authenticatedToken = $authToken;
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
        return ($user->getAuthId() && $user->getAuthIdentifier());
    }

    /**
     * Updates the user's password hash.
     *
     * Assumes that the existing hash needs to be rehashed.
     *
     * @param  AuthenticatableInterface $user     The user to update.
     * @param  string                   $password The plain-text password to hash.
     * @param  boolean                  $update   Whether to persist changes to storage.
     * @throws InvalidArgumentException If the password is invalid.
     * @return boolean Returns TRUE if the password was changed, or FALSE otherwise.
     */
    public function rehashUserPassword(AuthenticatableInterface $user, $password, $update = true)
    {
        if (!$this->validateAuthPassword($password)) {
            throw new InvalidArgumentException(
                'Can not rehash password: password is invalid'
            );
        }

        $userId = $user->getAuthId();

        if ($update && $userId) {
            $userClass = get_class($user);

            $this->logger->info(sprintf(
                '[Authenticator] Rehashing password for user "%s" (%s)',
                $userId,
                $userClass
            ));
        }

        $passwordKey = $user->getAuthPasswordKey();

        $user[$passwordKey] = password_hash($password, PASSWORD_DEFAULT);

        if ($update && $userId) {
            $result = $user->update([
                $passwordKey,
            ]);

            if ($result) {
                $this->logger->notice(sprintf(
                    '[Authenticator] Password was rehashed for user "%s" (%s)',
                    $userId,
                    $userClass
                ));
            } else {
                $this->logger->warning(sprintf(
                    '[Authenticator] Password failed to be rehashed for user "%s" (%s)',
                    $userId,
                    $userClass
                ));
            }

            return $result;
        }

        return true;
    }

    /**
     * Updates the user's password hash.
     *
     * @param  AuthenticatableInterface $user     The user to update.
     * @param  string                   $password The plain-text password to hash.
     * @param  boolean                  $update   Whether to persist changes to storage.
     * @throws InvalidArgumentException If the password is invalid.
     * @return boolean Returns TRUE if the password was changed, or FALSE otherwise.
     */
    public function changeUserPassword(AuthenticatableInterface $user, $password, $update = true)
    {
        if (!$this->validateAuthPassword($password)) {
            throw new InvalidArgumentException(
                'Can not change password: password is invalid'
            );
        }

        $userId = $user->getAuthId();

        if ($update && $userId) {
            $userClass = get_class($user);

            $this->logger->info(sprintf(
                '[Authenticator] Changing password for user "%s" (%s)',
                $userId,
                $userClass
            ));
        }

        $passwordKey = $user->getAuthPasswordKey();

        $user[$passwordKey] = password_hash($password, PASSWORD_DEFAULT);

        if ($update && $userId) {
            $result = $user->update([
                $passwordKey,
            ]);

            if ($result) {
                $this->logger->notice(sprintf(
                    '[Authenticator] Password was changed for user "%s" (%s)',
                    $userId,
                    $userClass
                ));
            } else {
                $this->logger->warning(sprintf(
                    '[Authenticator] Password failed to be changed for user "%s" (%s)',
                    $userId,
                    $userClass
                ));
            }

            return $result;
        }

        return true;
    }

    /**
     * Clear the authenticator's internal cache.
     *
     * @return void
     */
    protected function clearAuthenticator()
    {
        $this->authenticatedMethod = null;
        $this->authenticatedToken  = null;
        $this->authenticatedUser   = null;
        $this->isLoggedOut         = true;
    }
}
