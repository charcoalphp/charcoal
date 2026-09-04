<?php

namespace Charcoal\Tests\App;

// From PSR-7
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-app'
use Charcoal\App\App;
use Charcoal\App\AppConfig;
use Charcoal\App\AppContainer;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AppTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var App
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $config = new AppConfig([
            'base_path' => sys_get_temp_dir(),
        ]);
        $container = new AppContainer([
            'config' => $config
        ]);

        $this->obj = new App($container);
    }

    public function testAppIsConstructed()
    {
        $app = new App();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(App::class, $this->obj);
    }

    public function testRun()
    {
        $res = $this->obj->run(true);
        $this->assertInstanceOf(ResponseInterface::class, $res);
    }

    /**
     * The shared test bootstrap starts a session before any test runs (for
     * tests elsewhere that need one active), so exercising the "no session
     * yet" branch of App::setupSessionCookieParams() requires temporarily
     * closing it — this is also, not coincidentally, exactly the scenario
     * testRun() above already covers: run() must not warn/error when a
     * session is already active by the time it's called.
     *
     * @runInSeparateProcess
     * @return void
     */
    public function testRunSetsSecureSessionCookieParamsByDefault()
    {
        $wasActive = (session_status() === PHP_SESSION_ACTIVE);
        if ($wasActive) {
            session_write_close();
        }

        try {
            $this->obj->run(true);

            $params = session_get_cookie_params();
            $this->assertTrue($params['secure']);
            $this->assertTrue($params['httponly']);
            $this->assertEquals('Lax', $params['samesite']);
        } finally {
            if ($wasActive && session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
        }
    }

    /**
     * @runInSeparateProcess
     * @return void
     */
    public function testRunAppliesSessionCookieConfigOverrides()
    {
        $wasActive = (session_status() === PHP_SESSION_ACTIVE);
        if ($wasActive) {
            session_write_close();
        }

        try {
            $config = new AppConfig([
                'base_path' => sys_get_temp_dir(),
                'session'   => [
                    'cookie_secure'   => false,
                    'cookie_httponly' => false,
                    'cookie_samesite' => 'Strict',
                ],
            ]);
            $container = new AppContainer([ 'config' => $config ]);
            $app = new App($container);

            $app->run(true);

            $params = session_get_cookie_params();
            $this->assertFalse($params['secure']);
            $this->assertFalse($params['httponly']);
            $this->assertEquals('Strict', $params['samesite']);
        } finally {
            if ($wasActive && session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
        }
    }
}
