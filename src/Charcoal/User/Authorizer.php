<?php

namespace Charcoal\User;

use InvalidArgumentException;

// From 'zendframework/zend-permissions'
use Zend\Permissions\Acl\Acl;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// From 'charcoal-user'
use Charcoal\User\UserInterface;

/**
 * The authorizer service helps with user authorization (permission checking).
 *
 * ## Constructor dependencies
 *
 * Constructor dependencies are passed as an array of `key=>value` pair.
 * The required dependencies are:
 *
 * - `logger` A PSR3 logger instance.
 * - `acl` A Zend ACL (Access-Control-List) instance.
 * - `resource` The ACL resource identifier (string).
 *
 * ## Checking permissions
 *
 * To check if a given ACL (passed in constructor) allows a list of permissions (aka privileges):
 *
 * - `userAllowed(UserInterface $user, string[] $aclPermissions)`
 * - `rolesAllowed(string[] $roles, string[] $aclPermissions)`
 */
class Authorizer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Acl $acl
     */
    private $acl;

    /**
     * The ACL resource identifier
     * @var string $resource
     */
    private $resource;

    /**
     * @param array $data Class dependencies.
     */
    public function __construct(array $data)
    {
        $this->setLogger($data['logger']);
        $this->setAcl($data['acl']);
        $this->setResource($data['resource']);
    }

    /**
     * @param Acl $acl The ACL instance.
     * @return void
     */
    private function setAcl(Acl $acl)
    {
        $this->acl = $acl;
    }

    /**
     * @return Acl
     */
    protected function acl()
    {
        return $this->acl;
    }

    /**
     * @param string $resource The ACL resource identifier.
     * @throws InvalidArgumentException If the resource identifier is not a string.
     * @return void
     */
    private function setResource($resource)
    {
        if (!is_string($resource)) {
            throw new InvalidArgumentException(
                'ACL resource identifier must be a string.'
            );
        }
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    protected function resource()
    {
        return $this->resource;
    }

    /**
     * @param string[] $aclRoles       The ACL roles to validate against.
     * @param string[] $aclPermissions The acl permissions to validate.
     * @return boolean Wether the permissions are allowed for a given list of roles.
     */
    public function rolesAllowed(array $aclRoles, array $aclPermissions)
    {
        $acl = $this->acl();
        $aclResource = $this->resource();

        foreach ($aclRoles as $aclRole) {
            foreach ($aclPermissions as $aclPermission) {
                if (!$acl->isAllowed($aclRole, $aclResource, $aclPermission)) {
                    $this->logger->error(sprintf(
                        'Role "%s" is not allowed permission "%s"',
                        $aclRole,
                        $aclPermission
                    ));
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param UserInterface $user           The user to validate against.
     * @param string[]      $aclPermissions The acl permissions to validate.
     * @return boolean Whether the permissions are allowed for a given user.
     */
    public function userAllowed(UserInterface $user, array $aclPermissions)
    {
        return $this->rolesAllowed($user->roles(), $aclPermissions);
    }
}
