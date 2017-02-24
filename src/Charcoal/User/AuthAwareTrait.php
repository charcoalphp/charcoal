<?php

namespace Charcoal\User;

use RuntimeException;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\Authenticator;
use Charcoal\User\Authorizer;

/**
 * An implementation, as Trait, of the {@see \Charcoal\User\AuthAwareInterface}.
 */
trait AuthAwareTrait
{
    /**
     * @var Authenticator
     */
    private $authenticator;

    /**
     * @var Authorizer
     */
    private $authorizer;

    /**
     * @var string[]
     */
    private $requiredAclPermissions;

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
     * @param  Authenticator $authenticator The authentication service.
     * @return AuthAwareInterface
     */
    protected function setAuthenticator(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;

        return $this;
    }

    /**
     * Retrieve the authentication service.
     *
     * @throws RuntimeException If the authenticator was not previously set.
     * @return Authenticator
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
     * @param  Authorizer $authorizer The authorization service.
     * @return AuthAwareInterface
     */
    protected function setAuthorizer(Authorizer $authorizer)
    {
        $this->authorizer = $authorizer;

        return $this;
    }

    /**
     * Retrieve the authorization service.
     *
     * @throws RuntimeException If the authorizer was not previously set.
     * @return Authorizer
     */
    protected function authorizer()
    {
        if (!$this->authenticator) {
            throw new RuntimeException(sprintf(
                'Authorizer service is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->authorizer;
    }

     /**
      * @param string[]|string|null $permissions The list of required permissions.
      * @throws InvalidArgumentException If the permissions are not an array or a comma-separated string.
      * @return AuthAwareTrait Chainable
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

    /**
     * @return boolean
     */
    public function isAuthorized()
    {
        return $this->hasPermissions($this->requiredAclPermissions());
    }

    /**
     * @param array|null $permissions The list of required permissions to check.
     * @return boolean
     */
    public function hasPermissions($permissions)
    {
        $authUser = $this->authenticator()->authenticate();
        if (!$authUser) {
            return false;
        }
        if ($permissions === null || empty($permissions)) {
            return true;
        }
        $authorized = $this->authorizer()->userAllowed($authUser, $permissions);
        return $authorized;
    }
}
