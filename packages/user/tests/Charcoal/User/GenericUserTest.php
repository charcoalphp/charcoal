<?php

namespace Charcoal\Tests\User;

use DI\Container;
// From 'charcoal-user'
use Charcoal\User\GenericUser;
use Charcoal\User\UserInterface;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\User\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GenericUser::class)]
class GenericUserTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var UserInterface
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
     *
     * @return void
     */
    protected function setUp(): void
    {
        if (session_id()) {
            session_unset();
        }

        $container = $this->container();

        $this->obj = new GenericUser([
            # 'container'        => $container,
            'logger'           => $container->get('logger'),
            'translator'       => $container->get('translator'),
            # 'property_factory' => $container->get('property/factory'),
            # 'metadata_loader'  => $container->get('metadata/loader')
        ]);
    }

    /**
     * @return void
     */
    public function testSessionKey()
    {
        $obj = $this->obj;

        $sessionKey = $obj::sessionKey();
        $this->assertSame('charcoal.user', $sessionKey);
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
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
