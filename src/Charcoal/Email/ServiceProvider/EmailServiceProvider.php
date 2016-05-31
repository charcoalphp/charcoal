<?php

namespace Charcoal\Email\ServiceProvider;

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

// From `phpmailer/phpmailer`
use \PHPMailer\PHPMailer\PHPMailer;

use \Charcoal\Email\Email;
use \Charcoal\Email\EmailInterface;
use \Charcoal\Email\EmailConfig;

use \Charcoal\Factory\GenericFactory;

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
            return new GenericFactory([
                'map' => [
                    'email' => Email::class
                ],
                'base_class' => EmailInterface::class,
                'default_class' => Email::class,
                'arguments' => [[
                    'logger'    => $container['logger'],
                    'config'    => $container['email/config'],
                    'view'      => $container['email/view'],
                    'template_factory' => $container['template/factory'],
                    'queue_item_factory' => $container['model/factory'],
                    'log_factory' => $container['model/factory']
                ]]
            ]);
        };

        /**
         * @param Container $container Pimple DI container.
         * @return \Charcoal\Email\EmailInterface
         */
        $container['email'] = $container->factory(function (Container $container) {
            return $container['email/factory']->create('email');
        });
    }
}
