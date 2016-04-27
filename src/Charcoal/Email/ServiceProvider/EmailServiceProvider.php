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
         * @return EmailConfig
         */
        $container['email/config'] = function (Container $container) {
            $config = $container['config'];
            $emailConfig = new EmailConfig($config['email']);
            return $emailConfig;
        };

        /**
         * @return \Charcoal\View\ViewInterface
         */
        $container['email/view'] = function (Container $container) {
            return $container['view'];
        };

        /**
         * @return Email
         */
        $container['email'] = $container->factory(function (Container $container) {
            $email = new Email();
            return $email;
        });

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
    }
}
