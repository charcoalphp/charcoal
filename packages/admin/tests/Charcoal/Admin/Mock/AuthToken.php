<?php

declare(strict_types=1);

namespace Charcoal\Tests\Admin\Mock;

// From 'charcoal-admin'
use Charcoal\Admin\User\AuthToken as AdminAuthtoken;

/**
 * Mock AuthToken
 *
 * This class was created to mock the `setcookie()` function
 * used by {@see \Charcoal\User\AuthTokenCookieTrait}.
 */
class AuthToken extends AdminAuthtoken
{
    /**
     * @return boolean
     */
    #[\Override]
    public function sendCookie()
    {
        return $this->isEnabled();
    }

    /**
     * @return boolean
     */
    #[\Override]
    public function deleteCookie()
    {
        return $this->isEnabled();
    }
}
