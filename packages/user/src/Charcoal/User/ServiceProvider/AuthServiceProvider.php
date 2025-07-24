<?php

namespace Charcoal\User\ServiceProvider;

use DI\Container;
// From 'laminas/laminas-permissions-acl'
use Laminas\Permissions\Acl\Acl;
// From 'charcoal-user'
use Charcoal\User\Authenticator;
use Charcoal\User\Authorizer;
use Charcoal\User\AuthToken;
use Charcoal\User\GenericUser as User;
use Psr\Container\ContainerInterface;

/**
 *
 */
class AuthServiceProvider
{
    /**
     * @param  Container $container A DI Container.
     * @return void
     */
    public function register(ContainerInterface $container)
    {
        if (!($container->has('authenticator'))) {
            /**
             * @param  Container $container The DI Container.
             * @return Authenticator
             */
            $container->set('authenticator', function (Container $container) {
                return new Authenticator([
                    'logger'        => $container->get('logger'),
                    'user_type'     => User::class,
                    'user_factory'  => $container->get('model/factory'),
                    'token_type'    => AuthToken::class,
                    'token_factory' => $container->get('model/factory'),
                ]);
            });
        }

        if (!($container->has('authorizer'))) {
            /**
             * @param  Container $container The DI Container.
             * @return Authorizer
             */
            $container->set('authorizer', function (Container $container) {
                return new Authorizer([
                    'logger'    => $container->get('logger'),
                    'acl'       => $container->get('authorizer/acl'),
                    'resource'  => 'charcoal',
                ]);
            });
        }

        if (!($container->has('authorizer/acl'))) {
            /**
             * @return Acl
             */
            $container->set('authorizer/acl', function () {
                return new Acl();
            });
        }
    }
}
