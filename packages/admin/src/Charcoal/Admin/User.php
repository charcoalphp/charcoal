<?php

declare(strict_types=1);

namespace Charcoal\Admin;

// From 'charcoal-user'
use Charcoal\User\AbstractUser;

/**
 * Admin User Model
 */
class User extends AbstractUser
{
    public static function sessionKey(): string
    {
        return 'admin.user';
    }
}
