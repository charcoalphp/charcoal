<?php

namespace Charcoal\Tests\Admin\ServiceProvider;

use Pimple\Container;

use Charcoal\App\AppConfig;
use Charcoal\Admin\ServiceProvider\AdminServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 * Exercises `registerMiddlewareServices()` directly rather than the full
 * `register()`, which pulls in unrelated dependencies (view, email, etc.) a
 * bare container can't satisfy — no existing admin test builds a container
 * complete enough for a full `register()` call.
 */
class AdminServiceProviderTest extends AbstractTestCase
{
    public function testRegisterMiddlewareServices()
    {
        $container = new Container([ 'config' => new AppConfig() ]);
        $provider  = new AdminServiceProvider();
        $provider->registerMiddlewareServices($container);

        $this->assertTrue(isset($container['admin/csrf/guard']));
        $this->assertTrue(isset($container['middlewares/charcoal/admin/middleware/csrf']));
    }

    public function testDefaultCsrfMiddlewareConfigIsInjectedWhenMissing()
    {
        $container = new Container([ 'config' => new AppConfig() ]);
        $provider  = new AdminServiceProvider();
        $provider->registerMiddlewareServices($container);

        $config = $container['config']['middlewares']['charcoal/admin/middleware/csrf'];

        $this->assertTrue($config['active']);
        $this->assertContains('^/admin/login$', $config['included_path']);
        $this->assertContains('^/admin/account/lost-password$', $config['included_path']);
        $this->assertContains('^/admin/account/reset-password(/.*)?$', $config['included_path']);
    }

    public function testExplicitAppConfigIsNotOverridden()
    {
        $appConfig = new AppConfig([
            'middlewares' => [
                'charcoal/admin/middleware/csrf' => [
                    'active'         => false,
                    'included_path'  => [ '^/admin/login$' ],
                    'failure_message' => 'Custom message.',
                ],
            ],
        ]);
        $container = new Container([ 'config' => $appConfig ]);
        $provider  = new AdminServiceProvider();
        $provider->registerMiddlewareServices($container);

        $config = $container['config']['middlewares']['charcoal/admin/middleware/csrf'];

        $this->assertFalse($config['active']);
        $this->assertEquals([ '^/admin/login$' ], $config['included_path']);
        $this->assertEquals('Custom message.', $config['failure_message']);
    }
}
