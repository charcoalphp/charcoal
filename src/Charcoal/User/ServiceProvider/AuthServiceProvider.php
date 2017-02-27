<?php

namespace Charcoal\User\ServiceProvider;

// From 'zendframework/zend-permissions-acl'
use Zend\Permissions\Acl\Acl;

// From Pimple
use Pimple\Container;
use Pimple\ServiceProviderInterface;

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
     * @return void
     */
    public function register(Container $container)
    {
        if (!isset($container['authenticator'])) {
            /**
             * @param  Container $container The Pimple DI Container.
             * @return Authenticator
             */
            $container['authenticator'] = function (Container $container) {
                return new Authenticator([
                    'logger'        => $container['logger'],
                    'user_type'     => User::class,
                    'user_factory'  => $container['model/factory'],
                    'token_type'    => AuthToken::class,
                    'token_factory' => $container['model/factory']
                ]);
            };
        }

        if (!isset($container['authorizer'])) {
            /**
             * @param  Container $container The Pimple DI container.
             * @return Authorizer
             */
            $container['authorizer'] = function (Container $container) {
                return new Authorizer([
                    'logger'    => $container['logger'],
                    'acl'       => new Acl(),
                    'resource'  => 'charcoal'
                ]);
            };
        }
    }
}
