<?php

namespace Charcoal\Tests\User;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\Authenticator;
use Charcoal\User\AuthToken;
use Charcoal\User\GenericUser as User;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\User\ContainerProvider;

/**
 *
 */
class AuthenticatorTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var Authenticator
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
    public function setUp()
    {
        if (session_id()) {
            session_unset();
        }

        $container = $this->container();

        $this->obj = new Authenticator([
            'logger'        => $container['logger'],
            'user_type'     => User::class,
            'user_factory'  => $container['model/factory'],
            'token_type'    => AuthToken::class,
            'token_factory' => $container['model/factory']
        ]);
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(Authenticator::class, $this->obj);
    }

    /**
     * @return void
     */
    public function testAuthenticate()
    {
        $ret = $this->obj->authenticate();
        $this->assertNull($ret);
    }

    /**
     * @return void
     */
    public function testAuthenticateByPasswordInvalidUsername()
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->authenticateByPassword([], '');
    }

    /**
     * @return void
     */
    public function testAuthenticateByPasswordInvalidPassword()
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->authenticateByPassword('', []);
    }

    /**
     * @return void
     */
    public function testAuthenticateByPasswordEmpty()
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->authenticateByPassword('', '');
    }

    /**
     * @return void
     */
    public function testAuthenticateByPassword()
    {
        $this->assertNull($this->obj->authenticateByPassword('test', 'password'));
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
