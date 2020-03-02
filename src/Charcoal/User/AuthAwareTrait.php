<?php

namespace Charcoal\User;

use InvalidArgumentException;
use RuntimeException;

// From Pimple
use Pimple\Container;

/**
 * An implementation, as Trait, of the {@see \Charcoal\User\AuthAwareInterface}.
 */
trait AuthAwareTrait
{
    /**
     * @var AuthenticatorInterface
     */
    private $authenticator;

    /**
     * @var AuthorizerInterface
     */
    private $authorizer;

    /**
     * @var string[]
     */
    private $requiredAclPermissions;

    /**
     * @return boolean
     */
    public function isAuthorized()
    {
        return $this->hasPermissions($this->requiredAclPermissions());
    }

    /**
     * @param  array|null $permissions The list of required permissions to check.
     * @return boolean
     */
    public function hasPermissions($permissions)
    {
        $authUser = $this->authenticator()->user();
        if (!$authUser) {
            return false;
        }
        if ($permissions === null || empty($permissions)) {
            return true;
        }
        $authorized = $this->authorizer()->userAllowed($authUser, $permissions);
        return $authorized;
    }

    /**
     * @param  Container $container The DI container.
     * @return void
     */
    protected function setAuthDependencies(Container $container)
    {
        $this->setAuthenticator($container['authenticator']);
        $this->setAuthorizer($container['authorizer']);
    }

    /**
     * Set the authentication service.
     *
     * @param  AuthenticatorInterface $authenticator The authentication service.
     * @return void
     */
    protected function setAuthenticator(AuthenticatorInterface $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * Retrieve the authentication service.
     *
     * @throws RuntimeException If the authenticator was not previously set.
     * @return AuthenticatorInterface
     */
    protected function authenticator()
    {
        if (!$this->authenticator) {
            throw new RuntimeException(sprintf(
                'Authenticator service is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->authenticator;
    }

    /**
     * Set the authorization service.
     *
     * @param  AuthorizerInterface $authorizer The authorization service.
     * @return void
     */
    protected function setAuthorizer(AuthorizerInterface $authorizer)
    {
        $this->authorizer = $authorizer;
    }

    /**
     * Retrieve the authorization service.
     *
     * @throws RuntimeException If the authorizer was not previously set.
     * @return AuthorizerInterface
     */
    protected function authorizer()
    {
        if (!$this->authorizer) {
            throw new RuntimeException(sprintf(
                'Authorizer service is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->authorizer;
    }

    /**
     * @param  string[]|string|null $permissions The list of required permissions.
     * @throws InvalidArgumentException If the permissions are not an array or a comma-separated string.
     * @return self
     */
    protected function setRequiredAclPermissions($permissions)
    {
        if ($permissions === null || !$permissions) {
            $this->permissions = null;
            return $this;
        }
        if (is_string($permissions)) {
            $permissions = explode(',', $permissions);
            $permissions = array_map('trim', $permissions);
        }
        if (!is_array($permissions)) {
            throw new InvalidArgumentException(
                'Invalid ACL permissions. Must be an array of permissions or a comma-separated string or permissions.'
            );
        }
        $this->requiredAclPermissions = $permissions;
        return $this;
    }

    /**
     * @return string[]|null
     */
    protected function requiredAclPermissions()
    {
        return $this->requiredAclPermissions;
    }
}
