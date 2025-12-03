<?php

namespace Charcoal\Tests\App\Template;

use DI\Container;
// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
// From 'charcoal-app'
use Charcoal\App\Template\AbstractTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\App\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AbstractTemplate::class)]
class AbstractTemplateTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var AbstractTemplate
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
        $container = $this->container();

        $this->obj = $this->getMockForAbstractClass(AbstractTemplate::class, [[
            'logger'    => $container->get('logger'),
            'container' => $container
        ]]);
    }

    public function testInitIsTrue()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $this->assertTrue($this->obj->init($request));
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerLogger($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
