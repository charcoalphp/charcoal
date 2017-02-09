<?php

namespace Charcoal\User;

/**
 * Defines a class with authentication capabilities.
 *
 * Implementation, as trait, provided by {@see \Charcoal\Admin\User\AuthAwareTrait}.
 */
interface AuthAwareInterface
{
    /**
     * @return boolean
     */
    public function isAuthorized();

    /**
     * @param array $permissions The list of required permissions to check.
     * @return boolean
     */
    public function hasPermissions(array $permissions);
}
