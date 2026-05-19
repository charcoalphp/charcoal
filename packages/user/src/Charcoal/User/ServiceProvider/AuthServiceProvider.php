<?php

namespace Charcoal\User\ServiceProvider;

// From Pimple
use Pimple\Container;
use Pimple\ServiceProviderInterface;
// From 'laminas/laminas-permissions-acl'
use Laminas\Permissions\Acl\Acl;
// From 'charcoal-user'
use Charcoal\User\Authenticator;
use Charcoal\User\Authorizer;
use Charcoal\User\AuthToken;
use Charcoal\User\GenericUser as User;

/**
 *
 */
class AuthServiceProvider implements ServiceProviderInterface
{
    /**
     * @param  Container $container A Pimple DI container.
     */
    public function register(Container $container): void
    {
        if (!isset($container['authenticator'])) {
            /**
             * @param  Container $container The Pimple DI Container.
             * @return Authenticator
             */
            $container['authenticator'] = (fn(Container $container): \Charcoal\User\Authenticator => new Authenticator([
                'logger'        => $container['logger'],
                'user_type'     => User::class,
                'user_factory'  => $container['model/factory'],
                'token_type'    => AuthToken::class,
                'token_factory' => $container['model/factory'],
            ]));
        }

        if (!isset($container['authorizer'])) {
            /**
             * @param  Container $container The Pimple DI container.
             * @return Authorizer
             */
            $container['authorizer'] = (fn(Container $container): \Charcoal\User\Authorizer => new Authorizer([
                'logger'    => $container['logger'],
                'acl'       => $container['authorizer/acl'],
                'resource'  => 'charcoal',
            ]));
        }

        if (!isset($container['authorizer/acl'])) {
            /**
             * @return Acl
             */
            $container['authorizer/acl'] = (fn(): \Laminas\Permissions\Acl\Acl => new Acl());
        }
    }
}
