<?php

declare(strict_types=1);

namespace Charcoal\Email\ServiceProvider;

use Charcoal\View\ViewInterface;
use DI\Container;
// From 'charcoal/factory'
use Charcoal\Factory\FactoryInterface;
use Charcoal\Factory\GenericFactory;
use Charcoal\Email\Email;
use Charcoal\Email\EmailInterface;
use Charcoal\Email\EmailConfig;
use Charcoal\Email\Services\Parser;
use Charcoal\Email\Services\Tracker;
use Psr\Container\ContainerInterface;

/**
 * Email Service Provider
 *
 * Can provide the following services to a DI container:
 *
 * - `email/config`
 * - `email/view`
 * - `email/factory`
 * - `email` (_factory_)
 */
class EmailServiceProvider
{
    /**
     * @param Container $container A pimple container instance.
     * @return void
     */
    public function register(ContainerInterface $container): void
    {
        /**
         * @param Container $container DI Container.
         * @return EmailConfig
         */
        $container->set('email/config', function (Container $container): EmailConfig {
            $appConfig = $container->get('config');
            $emailConfig = new EmailConfig($appConfig['email']);
            return $emailConfig;
        });

        /**
         * @param Container $container DI Container.
         * @return ViewInterface
         */
        $container->set('email/view', function (Container $container): ViewInterface {
            return $container->get('view');
        });

        /**
         * @param Container $container DI Container.
         * @return FactoryInterface
         */
        $container->set('email/factory', function (Container $container): FactoryInterface {
            return new GenericFactory([
                'map' => [
                    'email' => Email::class
                ],
                'base_class' => EmailInterface::class,
                'default_class' => Email::class,
                'arguments' => [[
                    'logger'             => $container->get('logger'),
                    'config'             => $container->get('email/config'),
                    'view'               => $container->get('email/view'),
                    'template_factory'   => $container->get('template/factory'),
                    'queue_item_factory' => $container->get('model/factory'),
                    'log_factory'        => $container->get('model/factory'),
                    'tracker'            => $container->get('email/tracker')
                ]]
            ]);
        });

        /**
         * @return Parser
         */
        $container->set('email/parser', function (): Parser {
            return new Parser();
        });

        /**
         * @param Container $container DI Container.
         * @return Tracker
         */
        $container->set('email/tracker', function (Container $container): Tracker {
            return new Tracker(
                (string)$container->get('base-url'),
                $container->get('model/factory')
            );
        });

        /**
         * @param Container $container DI Container.
         * @return \Charcoal\Email\EmailInterface
         */
        $container->set('email', function (Container $container): EmailInterface {
            return $container->get('email/factory')->create('email');
        });
    }
}
