<?php

namespace Charcoal\User;

// From 'laminas/laminas-permissions-acl'
use Laminas\Permissions\Acl\AclInterface;

/**
 * Authorizer Interface
 */
interface AuthorizerInterface extends AclInterface
{
    /**
     * Determine if the role is registered.
     *
     * @see \Laminas\Permissions\Acl\Acl::hasRole()
     *
     * @param  Laminas\Permissions\Acl\Role\RoleInterface|string $role The ACL role to check.
     * @return boolean Returns TRUE if and only if the $role exists in the registry.
     */
    public function hasRole($role);
}
