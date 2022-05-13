<?php

declare(strict_types=1);

namespace Charcoal\Email\ServiceProvider;

// From 'pimple/pimple'
use Charcoal\View\ViewInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

// From 'phpmailer/phpmailer'
use PHPMailer\PHPMailer\PHPMailer;

// From 'locomotivemtl/charcoal-factory'
use Charcoal\Factory\FactoryInterface;
use Charcoal\Factory\GenericFactory;

use Charcoal\Email\Email;
use Charcoal\Email\EmailInterface;
use Charcoal\Email\EmailConfig;
use Charcoal\Email\Services\Parser;
use Charcoal\Email\Services\Tracker;

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
    public function register(Container $container): void
    {
        /**
         * @param Container $container Pimple DI container.
         * @return EmailConfig
         */
        $container['email/config'] = function (Container $container): EmailConfig {
            $appConfig = $container['config'];
            $emailConfig = new EmailConfig($appConfig['email']);
            return $emailConfig;
        };

        /**
         * @param Container $container Pimple DI container.
         * @return ViewInterface
         */
        $container['email/view'] = function (Container $container): ViewInterface {
            return $container['view'];
        };

        /**
         * @param Container $container Pimple DI Container.
         * @return FactoryInterface
         */
        $container['email/factory'] = function(Container $container): FactoryInterface {
            return new GenericFactory([
                'map' => [
                    'email' => Email::class
                ],
                'base_class' => EmailInterface::class,
                'default_class' => Email::class,
                'arguments' => [[
                    'logger'             => $container['logger'],
                    'config'             => $container['email/config'],
                    'view'               => $container['email/view'],
                    'template_factory'   => $container['template/factory'],
                    'queue_item_factory' => $container['model/factory'],
                    'log_factory'        => $container['model/factory'],
                    'tracker'            => $container['email/tracker']
                ]]
            ]);
        };

        /**
         * @return Parser
         */
        $container['email/parser'] = function(): Parser {
            return new Parser();
        };

        /**
         * @param Container $container Pimple DI Container.
         * @return Tracker
         */
        $container['email/tracker'] = function(Container $container): Tracker {
            return new Tracker(
                (string)$container['base-url'],
                $container['model/factory']
            );
        };

        /**
         * @param Container $container Pimple DI container.
         * @return \Charcoal\Email\EmailInterface
         */
        $container['email'] = $container->factory(function (Container $container): EmailInterface {
            return $container['email/factory']->create('email');
        });
    }
}
