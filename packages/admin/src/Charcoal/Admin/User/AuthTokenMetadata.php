<?php

declare(strict_types=1);

namespace Charcoal\Admin\User;

// From 'charcoal-user'
use Charcoal\User\AuthTokenMetadata as BaseAuthTokenMetadata;

/**
 * Admin Authorization Token Metadata
 */
class AuthTokenMetadata extends BaseAuthTokenMetadata
{
    #[\Override]
    public function defaults(): array
    {
        $parentDefaults = parent::defaults();
        return array_replace_recursive($parentDefaults, [
            'cookie_name' => 'charcoal_admin_login',
        ]);
    }
}
