<?php

namespace Charcoal\User;

use InvalidArgumentException;
use RuntimeException;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

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
     * Returns a new Authoricator object.
     *
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
     * Set the user object type (model).
     *
     * @param string $type The user object type.
     * @throws InvalidArgumentException If the user object type parameter is not a string.
     * @return Authenticator Chainable
     */
    private function setUserType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'User object type must be a string'
            );
        }

        $this->userType = $type;

        return $this;
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
     * Retrieve the auth-token object type.
     *
     * @return string
     */
    protected function tokenType()
    {
        return $this->tokenType;
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
     * Determine if the current user is authenticated.
     *
     * The user is authenticated via _session ID_ or _auth token_.
     *
     * @return \Charcoal\User\UserInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    public function authenticate()
    {
        $u = $this->authenticateBySession();
        if ($u) {
            return $u;
        }

        $u = $this->authenticateByToken();
        if ($u) {
            return $u;
        }

        return null;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param string $username Username, part of necessery credentials.
     * @param string $password Password, part of necessary credentials.
     * @throws InvalidArgumentException If username or password are invalid or empty.
     * @return \Charcoal\User\UserInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    public function authenticateByPassword($username, $password)
    {
        if (!is_string($username) || !is_string($password)) {
            throw new InvalidArgumentException(
                'Username and password must be strings'
            );
        }

        if ($username == '' || $password == '') {
            throw new InvalidArgumentException(
                'Username and password can not be empty.'
            );
        }

        $u = $this->userFactory()->create($this->userType());
        if (!$u->source()->tableExists()) {
            $u->source()->createTable();
        }

        // Force lowercase
        $username = mb_strtolower($username);

        // Load the user by username
        $u->load($username);

        if ($u->username() != $username) {
            return null;
        }

        if ($u->active() === false) {
            return null;
        }

        // Validate password
        if (password_verify($password, $u->password())) {
            if (password_needs_rehash($u->password(), PASSWORD_DEFAULT)) {
                $this->logger->notice(sprintf(
                    'Rehashing password for user "%s" (%s)',
                    $u->username(),
                    $this->userType()
                ));
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $u->setPassword($hash);
                $u->update(['password']);
            }

            $u->login();

            return $u;
        } else {
            $this->logger->warning('Invalid login attempt for user: invalid password.');

            return null;
        }
    }

    /**
     * Attempt to authenticate a user using their session ID.
     *
     * @return \Charcoal\User\UserInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    private function authenticateBySession()
    {
        $u = $this->userFactory()->create($this->userType());
        // Call static method on user
        $u = call_user_func([get_class($u), 'getAuthenticated'], $this->userFactory());

        if ($u && $u->id()) {
            $u->saveToSession();

            return $u;
        } else {
            return null;
        }
    }

    /**
     * Attempt to authenticate a user using their auth token.
     *
     * @return \Charcoal\User\UserInterface|null Returns the authenticated user object
     *     or NULL if not authenticated.
     */
    private function authenticateByToken()
    {
        $tokenType = $this->tokenType();
        $authToken = $this->tokenFactory()->create($tokenType);

        if ($authToken->metadata()->enabled() !== true) {
            return null;
        }

        $tokenData = $authToken->getTokenDataFromCookie();
        if (!$tokenData) {
            return null;
        }
        $username = $authToken->getUsernameFromToken($tokenData['ident'], $tokenData['token']);
        if (!$username) {
            return null;
        }

        $u = $this->userFactory()->create($this->userType());
        $u->load($username);

        if ($u->id()) {
            $u->saveToSession();
            return $u;
        } else {
            return null;
        }
    }
}
