<?php

namespace Charcoal\Email\ServiceProvider;

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

// From `phpmailer/phpmailer`
use \PHPMailer\PHPMailer\PHPMailer;

use \Charcoal\Email\Email;
use \Charcoal\Email\EmailConfig;

use \Charcoal\Factory\MapFactory;

/**
 * Email Service Provider
 *
 * Can provide the following services to a Pimple container:
 *
 * - `email/config`
 * - `email/view`
 * - `email/factory`
 * - `email` (_factory_)
 */
class EmailServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container A pimple container instance.
     * @return void
     */
    public function register(Container $container)
    {
        /**
         * @param Container $container Pimple DI container.
         * @return \Charcoal\Email\EmailConfig
         */
        $container['email/config'] = function (Container $container) {
            $appConfig = $container['config'];
            $emailConfig = new EmailConfig($appConfig['email']);
            return $emailConfig;
        };

        /**
         * @param Container $container Pimple DI container.
         * @return \Charcoal\View\ViewInterface
         */
        $container['email/view'] = function (Container $container) {
            return $container['view'];
        };

        /**
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['email/factory'] = function(Container $container) {
            $factory = new MapFactory();
            $factory->setMap([
                'email'=>'\Charcoal\Email\Email'
            ]);
            $factory->setBaseClass('\Charcoal\Email\EmailInterface');
            $factory->setDefaultClass('\Charcoal\Email\Email');
            $factory->setArguments([
                'logger'    => $container['logger'],
                'config'    => $container['email/config'],
                'view'      => $container['email/view']
            ]);
            return $factory;
        };

        /**
         * @param Container $container Pimple DI container.
         * @return \Charcoal\Email\EmailInterface
         */
        $container['email'] = $container->factory(function (Container $container) {
            $email = new Email([
                'logger'    => $container['logger'],
                'config'    => $container['email/config'],
                'view'      => $container['email/view']
            ]);
            return $email;
        });
    }
}
