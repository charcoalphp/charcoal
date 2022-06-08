<?php

namespace Charcoal\User;

use InvalidArgumentException;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// From 'laminas/laminas-permissions-acl'
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Exception\ExceptionInterface as AclExceptionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface as AclResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface as AclRoleInterface;

// From 'charcoal-user'
use Charcoal\User\UserInterface;

/**
 * The base Authorizer service
 *
 * The authorizer service helps with user authorization (permission checking).
 *
 * ## Constructor dependencies
 *
 * Constructor dependencies are passed as an array of `key => value` pair.
 * The required dependencies are:
 *
 * - `logger` A PSR3 logger instance.
 * - `acl` A Laminas ACL (Access-Control-List) instance.
 *
 * ## Checking permissions
 *
 * To check if a given ACL (passed in constructor) allows a list of permissions (aka privileges):
 *
 * - `xxx(UserInterface $user, string[] $aclPermissions)`
 */
abstract class AbstractAuthorizer implements
    AuthorizerInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The ACL service.
     *
     * @var Acl
     */
    private $acl;

    /**
     * @param array $data Class dependencies.
     */
    public function __construct(array $data)
    {
        $this->setLogger($data['logger']);
        $this->setAcl($data['acl']);
    }

    /**
     * Check if access is granted to the role for all permissions.
     *
     * @param  AclRoleInterface|string          $role       One ACL role to check.
     * @param  AclResourceInterface|string|null $resource   One ACL resource to check.
     * @param  string|string[]                  $privileges One or many ACL privileges to check.
     * @return boolean|null Returns TRUE if and only if all the $privileges are granted against the $role.
     *     Returns NULL if no applicable role, resource, or permissions could be checked.
     */
    public function isRoleGrantedAll($role, $resource, $privileges)
    {
        $privileges = (array)$privileges;
        $result     = null;

        try {
            foreach ($privileges as $privilege) {
                if (!$this->isAllowed($role, $resource, $privilege)) {
                    return false;
                }

                $result = true;
            }

            return $result;
        } catch (AclExceptionInterface $e) {
            $this->logger->error('[ACL] '.$e->getMessage());
        }

        return null;
    }

    /**
     * Check if access is granted to all roles for all permissions.
     *
     * @param  AclRoleInterface|string|array    $roles      One or many ACL roles to check.
     * @param  AclResourceInterface|string|null $resource   One ACL resource to check.
     * @param  string|string[]                  $privileges One or many ACL privileges to check.
     * @return boolean|null Returns TRUE if and only if all the $privileges are granted against all $roles.
     *     Returns NULL if no applicable roles, resource, or permissions could be checked.
     */
    public function allRolesGrantedAll($roles, $resource, $privileges)
    {
        $roles      = (array)$roles;
        $privileges = (array)$privileges;
        $result     = null;

        foreach ($roles as $role) {
            if (!$this->isRoleGrantedAll($role, $resource, $privileges)) {
                return false;
            }

            $result = true;
        }

        return $result;
    }

    /**
     * Check if access is granted to any one of the roles for all permissions.
     *
     * @param  AclRoleInterface|string|array    $roles      One or many ACL roles to check.
     * @param  AclResourceInterface|string|null $resource   One ACL resource to check.
     * @param  string|string[]                  $privileges One or many ACL privileges to check.
     * @return boolean|null Returns TRUE if and only if all the $privileges are granted against any one of the $roles.
     *     Returns NULL if no applicable roles, resource, or permissions could be checked.
     */
    public function anyRolesGrantedAll($roles, $resource, $privileges)
    {
        $roles      = (array)$roles;
        $privileges = (array)$privileges;
        $result     = null;

        foreach ($roles as $role) {
            if ($this->isRoleGrantedAll($role, $resource, $privileges)) {
                return true;
            }

            $result = false;
        }

        return $result;
    }

    /**
     * Check if access is granted to the role for any one of the permissions.
     *
     * @param  AclRoleInterface|string          $role       One ACL role to check.
     * @param  AclResourceInterface|string|null $resource   One ACL resource to check.
     * @param  string|string[]                  $privileges One or many ACL privileges to check.
     * @return boolean|null Returns TRUE if any one of the $privileges are granted against the $role.
     *     Returns NULL if no applicable role, resource, or permissions could be checked.
     */
    public function isRoleGrantedAny($role, $resource, $privileges)
    {
        $privileges = (array)$privileges;
        $result     = null;

        try {
            foreach ($privileges as $privilege) {
                if ($this->isAllowed($role, $resource, $privilege)) {
                    return true;
                }

                $result = false;
            }

            return $result;
        } catch (AclExceptionInterface $e) {
            $this->logger->error('[ACL] '.$e->getMessage());
        }

        return null;
    }

    /**
     * Check if access is granted to all roles for any one of the permissions.
     *
     * @param  AclRoleInterface|string|array    $roles      One or many ACL roles to check.
     * @param  AclResourceInterface|string|null $resource   One ACL resource to check.
     * @param  string|string[]                  $privileges One or many ACL privileges to check.
     * @return boolean|null Returns TRUE if any one of the $privileges are granted against all $roles.
     *     Returns NULL if no applicable roles, resource, or permissions could be checked.
     */
    public function allRolesGrantedAny($roles, $resource, $privileges)
    {
        $roles      = (array)$roles;
        $privileges = (array)$privileges;
        $result     = null;

        foreach ($roles as $role) {
            if (!$this->isRoleGrantedAny($role, $resource, $privileges)) {
                return false;
            }

            $result = true;
        }

        return $result;
    }

    /**
     * Check if access is granted to any one of the roles for any one of the permissions.
     *
     * @param  AclRoleInterface|string|array    $roles      One or many ACL roles to check.
     * @param  AclResourceInterface|string|null $resource   One ACL resource to check.
     * @param  string|string[]                  $privileges One or many ACL privileges to check.
     * @return boolean|null
     *     Returns TRUE if any one of the $privileges are granted against any one of the $roles.
     *     Returns NULL if no applicable roles, resource, or permissions could be checked.
     */
    public function anyRolesGrantedAny($roles, $resource, $privileges)
    {
        $roles      = (array)$roles;
        $privileges = (array)$privileges;
        $result     = null;

        foreach ($roles as $role) {
            if ($this->isRoleGrantedAny($role, $resource, $privileges)) {
                return true;
            }

            $result = false;
        }

        return $result;
    }

    /**
     * Check if access is granted to the user's role(s) for permissions.
     *
     * @param  UserInterface                    $user       The user to check.
     * @param  AclResourceInterface|string|null $resource   One ACL resource to check.
     * @param  string|string[]                  $privileges One or many ACL privileges to check.
     * @return boolean|null
     *     Returns TRUE if and only if the $privileges are granted against one of the roles of the $user.
     *     Returns NULL if no applicable roles, resource, or permissions could be checked.
     */
    public function isUserGranted(UserInterface $user, $resource, $privileges)
    {
        return $this->anyRolesGrantedAll($user['roles'], $resource, $privileges);
    }



    // Helpers from \Laminas\Permissions\Acl\Acl
    // =========================================================================

    /**
     * Check if the role has access to the resource and privilege.
     *
     * This method is a proxy to {@see \Laminas\Permissions\Acl\Acl::isAllowed()}.
     *
     * @param  AclRoleInterface|string     $role      The ACL role to check.
     *     If $role is NULL, then the ACL will check for a "blacklist" rule
     *     (allow everything to all).
     * @param  AclResourceInterface|string $resource  The ACL resource to check.
     *     If $resource is NULL, then the ACL will check for a "blacklist" rule
     *     (allow everything to all).
     * @param  string                      $privilege The ACL privilege to check.
     *     If $privilege is NULL, then the ACL returns TRUE if and only if
     *     the $role is allowed all privileges on the $resource.
     * @return boolean Returns TRUE if and only if the $role has access to the $resource.
     */
    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        return $this->getAcl()->isAllowed($role, $resource, $privilege);
    }

    /**
     * Determine if the role is registered.
     *
     * @see \Laminas\Permissions\Acl\Acl::hasRole()
     *
     * @param  RoleInterface|string $role The ACL role to check.
     * @return boolean Returns TRUE if and only if the $role exists in the registry.
     */
    public function hasRole($role)
    {
        return $this->getAcl()->hasRole($role);
    }

    /**
     * Determine if the role inherits from another role.
     *
     * @see \Laminas\Permissions\Acl\Acl::inheritsRole()
     *
     * @param  RoleInterface|string $role        The ACL role to check.
     * @param  RoleInterface|string $inherit     The ACL role to check $role against.
     * @param  boolean              $onlyParents Whether the $role must inherit directly from $inherit.
     * @return boolean Returns TRUE if and only if $role inherits from $inherit.
     */
    public function inheritsRole($role, $inherit, $onlyParents = false)
    {
        return $this->getAcl()->inheritsRole($role, $inherit, $onlyParents);
    }

    /**
     * Determine if the resource is registered.
     *
     * @see \Laminas\Permissions\Acl\Acl::hasResource()
     *
     * @param  AclResourceInterface|string $resource The ACL resource to check.
     * @return boolean Returns TRUE if and only if the $resource exists in the ACL.
     */
    public function hasResource($resource)
    {
        return $this->getAcl()->hasResource($resource);
    }

    /**
     * Determine if the resource inherits from another resource.
     *
     * @see \Laminas\Permissions\Acl\Acl::inheritsResource()
     *
     * @param  AclResourceInterface|string $resource   The ACL resource to check.
     * @param  AclResourceInterface|string $inherit    The ACL resource to check $resource against.
     * @param  boolean                     $onlyParent Whether the $resource must inherit directly from $inherit.
     * @return boolean Returns TRUE if and only if $resource inherits from $inherit.
     */
    public function inheritsResource($resource, $inherit, $onlyParent = false)
    {
        return $this->getAcl()->inheritsResource($resource, $inherit, $onlyParent);
    }



    // Dependencies
    // =========================================================================

    /**
     * @return Acl
     */
    protected function getAcl()
    {
        return $this->acl;
    }

    /**
     * @param  Acl $acl The ACL service.
     * @return void
     */
    private function setAcl(Acl $acl)
    {
        $this->acl = $acl;
    }
}
