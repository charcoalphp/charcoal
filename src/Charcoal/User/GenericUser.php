<?php

namespace Charcoal\User;

/**
 * Concrete implementation of UserInterface
 */
class GenericUser extends AbstractUser
{
    /**
     * @return string
     */
    public static function sessionKey()
    {
        return 'charcoal.user';
    }
}
