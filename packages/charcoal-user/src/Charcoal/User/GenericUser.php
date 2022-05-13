<?php

namespace Charcoal\User;

/**
 * Concrete implementation of UserInterface
 */
class GenericUser extends AbstractUser
{
    /**
     * Retrieve the name of the session key for the user model.
     *
     * @return string
     */
    public static function sessionKey()
    {
        return 'charcoal.user';
    }
}
