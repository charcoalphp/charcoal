<?php

declare(strict_types=1);

namespace Charcoal\User;

/**
 * Concrete implementation of UserInterface
 */
class GenericUser extends AbstractUser
{
    /**
     * Retrieve the name of the session key for the user model.
     */
    public static function sessionKey(): string
    {
        return 'charcoal.user';
    }
}
