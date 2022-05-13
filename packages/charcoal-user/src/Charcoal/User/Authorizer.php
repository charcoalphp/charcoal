<?php

namespace Charcoal\User;

use InvalidArgumentException;

// From 'laminas/laminas-permissions-acl'
use Laminas\Permissions\Acl\Exception\ExceptionInterface as AclExceptionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface as AclResourceInterface;

// From 'charcoal-user'
use Charcoal\User\UserInterface;

/**
 * User Authorization Checker
 *
 * The authorizer service provides support, upon creation, for a default ACL resource.
 *
 * ## Checking permissions
 *
 * To check if a given ACL (passed in constructor) allows a list of permissions (aka privileges):
 *
 * - `userAllowed(UserInterface $user, string[] $aclPermissions)`
 * - `rolesAllowed(string[] $roles, string[] $aclPermissions)`
 */
class Authorizer extends AbstractAuthorizer
{
    const DEFAULT_RESOURCE = 'DEFAULT_RESOURCE';

    /**
     * The default ACL resource identifier.
     *
     * @var string|null
     */
    private $defaultResource;

    /**
     * @param array $data Class dependencies.
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        if (isset($data['resource'])) {
            $this->setDefaultResource($data['resource']);
        }
    }

    /**
     * Determine if access is granted by checking the role(s) for permission(s).
     *
     * @deprecated In favour of AbstractAuthorizer::anyRolesGrantedAll()
     *
     * @param  string[] $aclRoles       The ACL role(s) to check.
     * @param  string[] $aclPermissions The ACL privilege(s) to check.
     * @return boolean Returns TRUE if and only if the $aclPermissions are granted against one of the $aclRoles.
     *     Returns TRUE if an empty array of permissions is given.
     *     Returns NULL if no applicable roles or permissions could be checked.
     */
    public function rolesAllowed(array $aclRoles, array $aclPermissions)
    {
        if (empty($aclPermissions)) {
            return true;
        }

        return $this->anyRolesGrantedAll($aclRoles, static::DEFAULT_RESOURCE, $aclPermissions);
    }

    /**
     * Determine if access is granted by checking the user's role(s) for permission(s).
     *
     * @deprecated In favour of AbstractAuthorizer::isUserGranted()
     *
     * @param  UserInterface $user           The user to check.
     * @param  string[]      $aclPermissions The ACL privilege(s) to check.
     * @return boolean
     *     Returns TRUE if and only if the $aclPermissions are granted against one of the roles of the $user.
     *     Returns TRUE if an empty array of permissions is given.
     *     Returns NULL if no applicable roles or permissions could be checked.
     */
    public function userAllowed(UserInterface $user, array $aclPermissions)
    {
        if (empty($aclPermissions)) {
            return true;
        }

        return $this->isUserGranted($user, static::DEFAULT_RESOURCE, $aclPermissions);
    }

    /**
     * {@inheritdoc}
     *
     * This method overrides {@see AbstractAuthorizer::isAllowed()}
     * as proxy to {@see \Laminas\Permissions\Acl\Acl::isAllowed()}
     * to provide support for the special class constant `Authorizer::DEFAULT_RESOURCE`.
     *
     * @param  AclRoleInterface|string     $role      The ACL role to check.
     * @param  AclResourceInterface|string $resource  The ACL resource to check.
     * @param  string                      $privilege The ACL privilege to check.
     * @return boolean Returns TRUE if and only if the $role has access to the $resource.
     */
    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        if ($resource === static::DEFAULT_RESOURCE) {
            $resource = $this->getDefaultResource();
        }

        return parent::isAllowed($role, $resource, $privilege);
    }

    /**
     * @return string|null
     */
    protected function getDefaultResource()
    {
        return $this->defaultResource;
    }

    /**
     * @param  string|null $resource The ACL resource identifier.
     * @throws InvalidArgumentException If the resource identifier is not a string.
     * @return void
     */
    private function setDefaultResource($resource)
    {
        if (!is_string($resource) && $resource !== null) {
            throw new InvalidArgumentException(
                'ACL resource identifier must be a string or NULL'
            );
        }

        $this->defaultResource = $resource;
    }
}
