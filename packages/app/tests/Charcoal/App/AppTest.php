<?php

declare(strict_types=1);

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
     */
    private \Charcoal\App\App $obj;

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

    public function testAppIsConstructed(): void
    {
        $app = new App();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(App::class, $this->obj);
    }

    public function testRun(): void
    {
        $res = $this->obj->run(true);
        $this->assertInstanceOf(ResponseInterface::class, $res);
    }
}
