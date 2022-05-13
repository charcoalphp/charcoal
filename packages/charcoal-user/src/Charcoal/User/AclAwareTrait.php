<?php

namespace Charcoal\User;

use RuntimeException;

// From 'laminas/laminas-permissions-acl'
use Laminas\Permissions\Acl\Acl;

/**
 * Provides access control list.
 */
trait AclAwareTrait
{
    /**
     * The ACL service.
     *
     * @var Acl
     */
    private $acl;

    /**
     * Set the Access Control List service.
     *
     * @param  Acl $acl The ACL service.
     * @return void
     */
    protected function setAcl(Acl $acl)
    {
        $this->acl = $acl;
    }

    /**
     * Retrieve the Access Control List service.
     *
     * @throws RuntimeException If the authenticator was not previously set.
     * @return Acl
     */
    protected function acl()
    {
        if (!$this->acl) {
            throw new RuntimeException(sprintf(
                'ACL service is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->acl;
    }
}
