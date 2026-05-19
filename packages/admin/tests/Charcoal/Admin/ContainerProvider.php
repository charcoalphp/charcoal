<?php

namespace Charcoal\Tests\Admin;

use PDO;

// From Mockery
use Mockery;

// From PSR-3
use Psr\Log\NullLogger;

// From Slim
use Slim\Http\Uri;

// From 'tedivm/stash' (PSR-6)
use Stash\Pool;

// From 'laminas/laminas-permissions-acl'
use Laminas\Permissions\Acl\Acl;

// From Pimple
use Pimple\Container;

// From 'league/climate'
use League\CLImate\CLImate;
use League\CLImate\Util\System\Linux;
use League\CLImate\Util\Output;
use League\CLImate\Util\Reader\Stdin;
use League\CLImate\Util\UtilFactory;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-app'
use Charcoal\App\AppConfig;
use Charcoal\App\Template\WidgetBuilder;

// From 'charcoal-core'
use Charcoal\Source\DatabaseSource;
use Charcoal\Model\ServiceProvider\ModelServiceProvider;

// From 'charcoal-user'
use Charcoal\User\Authenticator;
use Charcoal\User\Authorizer;

// From 'charcoal-ui'
use Charcoal\Ui\Dashboard\DashboardBuilder;
use Charcoal\Ui\Dashboard\DashboardInterface;
use Charcoal\Ui\Layout\LayoutBuilder;
use Charcoal\Ui\Layout\LayoutFactory;

// From 'charcoal-email'
use Charcoal\Email\Email;
use Charcoal\Email\EmailConfig;

// From 'charcoal-view'
use Charcoal\View\ViewServiceProvider;

// From 'charcoal-translator'
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;

// From 'charcoal-admin'
use Charcoal\Admin\Config as AdminConfig;
use Charcoal\Admin\User as AdminUser;
use Charcoal\Tests\Admin\Mock\AuthToken as AdminAuthToken;

/**
 *
 */
class ContainerProvider
{
    /**
     * Register the unit tests required services.
     *
     * @param  Container $container A DI container.
     */
    public function registerDebug(Container $container): void
    {
        if (!isset($container['debug'])) {
            $container['debug'] = false;
        }
    }

    /**
     * Register the unit tests required services.
     *
     * @param  Container $container A DI container.
     */
    public function registerBaseServices(Container $container): void
    {
        $this->registerDebug($container);
        $this->registerConfig($container);
        $this->registerDatabase($container);
        $this->registerLogger($container);
        $this->registerCache($container);
    }

    /**
     * Register the admin services.
     *
     * @param  Container $container A DI container.
     */
    public function registerAdminServices(Container $container): void
    {
        $this->registerBaseServices($container);
        $this->registerBaseUrl($container);
        $this->registerAdminConfig($container);
    }

    /**
     * Setup the application's base URI.
     *
     * @param  Container $container A DI container.
     */
    public function registerBaseUrl(Container $container): void
    {
        $container['base-url'] = (fn() => Uri::createFromString(''));

        $container['admin/base-url'] = (fn() => Uri::createFromString('admin'));
    }

    /**
     * Setup the application configset.
     *
     * @param  Container $container A DI container.
     */
    public function registerConfig(Container $container): void
    {
        $container['config'] = (fn(): \Charcoal\App\AppConfig => new AppConfig([
            'base_path'  => realpath(__DIR__.'/../../..'),
            'apis'       => [
                'google' => [
                    'recaptcha' => [
                        'public_key'  => 'foobar',
                        'private_key' => 'bazqux',
                    ],
                ],
            ],
            'locales'    => [
                'en' => [
                    'locale' => 'en-US',
                ],
            ],
            'translator' => [
                'paths' => [],
            ],
            'metadata'   => [
                'paths'  => [
                    'metadata',
                    // Standalone
                    'vendor/charcoal/object/metadata',
                    'vendor/charcoal/user/metadata',
                    // Monorepo
                    '/../object/metadata',
                    '/../user/metadata',
                ],
            ],
        ]));

        /**
         * List of Charcoal module classes.
         *
         * Explicitly defined in case of a version mismatch with dependencies. This parameter
         * is normally defined by {@see \Charcoal\App\ServiceProvider\AppServiceProvider}.
         */
        $container['module/classes'] = [];
    }

    /**
     * Setup the admin module configset.
     *
     * @param  Container $container A DI container.
     */
    public function registerAdminConfig(Container $container): void
    {
        $this->registerConfig($container);

        $container['admin/config'] = (fn(): \Charcoal\Admin\Config => new AdminConfig());
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerElfinderConfig(Container $container): void
    {
        $container['elfinder/config'] = (fn(): array => []);
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerLayoutFactory(Container $container): void
    {
        $container['layout/factory'] = (fn(): \Charcoal\Ui\Layout\LayoutFactory => new LayoutFactory());
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerLayoutBuilder(Container $container): void
    {
        $this->registerLayoutFactory($container);

        $container['layout/builder'] = function (Container $container): \Charcoal\Ui\Layout\LayoutBuilder {
            $layoutFactory = $container['layout/factory'];
            return new LayoutBuilder($layoutFactory, $container);
        };
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerDashboardFactory(Container $container): void
    {
        $this->registerLogger($container);
        $this->registerWidgetBuilder($container);
        $this->registerLayoutBuilder($container);

        $container['dashboard/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'arguments'          => [[
                'container'      => $container,
                'logger'         => $container['logger'],
                'widget_builder' => $container['widget/builder'],
                'layout_builder' => $container['layout/builder']
            ]],
            'resolver_options' => [
                'suffix' => 'Dashboard'
            ]
        ]));
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerDashboardBuilder(Container $container): void
    {
        $this->registerDashboardFactory($container);

        $container['dashboard/builder'] = function (Container $container): \Charcoal\Ui\Dashboard\DashboardBuilder {
            $dashboardFactory = $container['dashboard/factory'];
            return new DashboardBuilder($dashboardFactory, $container);
        };
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerWidgetFactory(Container $container): void
    {
        $this->registerLogger($container);

        $container['widget/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'resolver_options' => [
                'suffix' => 'Widget'
            ],
            'arguments' => [[
                'container' => $container,
                'logger'    => $container['logger']
            ]]
        ]));
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerWidgetBuilder(Container $container): void
    {
        $this->registerWidgetFactory($container);

        $container['widget/builder'] = (fn(Container $container): \Charcoal\App\Template\WidgetBuilder => new WidgetBuilder($container['widget/factory'], $container));
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerClimate(Container $container): void
    {
        $container['climate/system'] = function () {
            $system = Mockery::mock(Linux::class);
            $system->shouldReceive('hasAnsiSupport')->andReturn(true);
            $system->shouldReceive('width')->andReturn(80);

            return $system;
        };

        $container['climate/output'] = function () {
            $output = Mockery::mock(Output::class);
            $output->shouldReceive('persist')->andReturn($output);
            $output->shouldReceive('sameLine')->andReturn($output);
            $output->shouldReceive('write');

            return $output;
        };

        $container['climate/reader'] = function () {
            $reader = Mockery::mock(Stdin::class);
            $reader->shouldReceive('line')->andReturn('line');
            $reader->shouldReceive('char')->andReturn('char');
            $reader->shouldReceive('multiLine')->andReturn('multiLine');
            return $reader;
        };

        $container['climate/util'] = (fn(Container $container): \League\CLImate\Util\UtilFactory => new UtilFactory($container['climate/system']));

        $container['climate'] = function (Container $container): \League\CLImate\CLImate {
            $climate = new CLImate();

            $climate->setOutput($container['climate/output']);
            $climate->setUtil($container['climate/util']);
            $climate->setReader($container['climate/reader']);

            return $climate;
        };
    }

    /**
     * Setup the application's logging interface.
     *
     * @param  Container $container A DI container.
     */
    public function registerLogger(Container $container): void
    {
        $container['logger'] = (fn(): \Psr\Log\NullLogger => new NullLogger());
    }

    /**
     * Setup the application's caching interface.
     *
     * @param  Container $container A DI container.
     */
    public function registerCache(Container $container): void
    {
        $container['cache'] = (fn(): \Stash\Pool => new Pool());
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerDatabase(Container $container): void
    {
        $container['database'] = function (): \PDO {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        };
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerModelServiceProvider(Container $container): void
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new ModelServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerTranslatorServiceProvider(Container $container): void
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new TranslatorServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerViewServiceProvider(Container $container): void
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new ViewServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerAcl(Container $container): void
    {
        $container['admin/acl'] = (fn(): \Laminas\Permissions\Acl\Acl => new Acl());

        $container['authorizer/acl'] = (fn($container) => $container['admin/acl']);
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerAuthenticator(Container $container): void
    {
        $this->registerLogger($container);
        $this->registerModelServiceProvider($container);

        $container['admin/authenticator'] = (fn(Container $container): \Charcoal\User\Authenticator => new Authenticator([
            'logger'        => $container['logger'],
            'user_type'     => AdminUser::class,
            'user_factory'  => $container['model/factory'],
            'token_type'    => AdminAuthToken::class,
            'token_factory' => $container['model/factory'],
        ]));

        $container['authenticator'] = (fn(Container $container): mixed => $container['admin/authenticator']);
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerAuthorizer(Container $container): void
    {
        $this->registerLogger($container);
        $this->registerAcl($container);

        $container['admin/authorizer'] = (fn(Container $container): \Charcoal\User\Authorizer => new Authorizer([
            'logger'    => $container['logger'],
            'acl'       => $container['admin/acl'],
            'resource'  => 'admin',
        ]));

        $container['authorizer'] = (fn(Container $container): mixed => $container['admin/authorizer']);
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerPropertyDisplayFactory(Container $container): void
    {
        $this->registerDatabase($container);
        $this->registerLogger($container);

        $container['property/display/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'resolver_options' => [
                'suffix' => 'Display'
            ],
            'arguments' => [[
                'container' => $container,
                'logger'    => $container['logger']
            ]]
        ]));
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerEmailFactory(Container $container): void
    {
        $container['email/factory'] = (fn(): \Charcoal\Factory\GenericFactory => new Factory([
            'map' => [
                'email' => Email::class,
            ],
        ]));
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerActionDependencies(Container $container): void
    {
        $this->registerDebug($container);
        $this->registerLogger($container);
        $this->registerDatabase($container);
        $this->registerCache($container);

        $this->registerAdminConfig($container);
        $this->registerBaseUrl($container);

        $this->registerAuthenticator($container);
        $this->registerAuthorizer($container);

        $this->registerViewServiceProvider($container);
        $this->registerModelServiceProvider($container);
        $this->registerTranslatorServiceProvider($container);
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerTemplateDependencies(Container $container): void
    {
        $this->registerDebug($container);
        $this->registerLogger($container);
        $this->registerDatabase($container);
        $this->registerCache($container);

        $this->registerAdminConfig($container);
        $this->registerBaseUrl($container);

        $this->registerAuthenticator($container);
        $this->registerAuthorizer($container);

        $this->registerViewServiceProvider($container);
        $this->registerModelServiceProvider($container);
        $this->registerTranslatorServiceProvider($container);

        $container['menu/builder'] = null;
        $container['menu/item/builder'] = null;
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerWidgetDependencies(Container $container): void
    {
        $this->registerDebug($container);
        $this->registerLogger($container);
        $this->registerDatabase($container);
        $this->registerCache($container);

        $this->registerAdminConfig($container);
        $this->registerBaseUrl($container);

        $this->registerAuthenticator($container);
        $this->registerAuthorizer($container);

        $this->registerViewServiceProvider($container);
        $this->registerModelServiceProvider($container);
        $this->registerTranslatorServiceProvider($container);
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerInputDependencies(Container $container): void
    {
        $this->registerDebug($container);
        $this->registerLogger($container);
        $this->registerDatabase($container);
        $this->registerCache($container);

        $this->registerAdminConfig($container);
        $this->registerBaseUrl($container);

        $this->registerAuthenticator($container);
        $this->registerAuthorizer($container);

        $this->registerViewServiceProvider($container);
        $this->registerModelServiceProvider($container);
        $this->registerTranslatorServiceProvider($container);
    }

    /**
     * @param  Container $container A DI container.
     */
    public function registerScriptDependencies(Container $container): void
    {
        $this->registerDebug($container);
        $this->registerLogger($container);
        $this->registerDatabase($container);
        $this->registerCache($container);

        $this->registerAdminConfig($container);
        $this->registerBaseUrl($container);

        $this->registerAuthenticator($container);
        $this->registerAuthorizer($container);

        $this->registerViewServiceProvider($container);
        $this->registerModelServiceProvider($container);
        $this->registerTranslatorServiceProvider($container);

        $this->registerClimate($container);
    }
}
