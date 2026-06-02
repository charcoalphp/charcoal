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
     */
    private \Charcoal\User\Authenticator $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        if (session_id()) {
            session_unset();
        }

        $this->obj = $this->createAuthenticator();
    }

    /**
     * Create a new Authenticator instance.
     */
    public function createAuthenticator(): \Charcoal\User\Authenticator
    {
        $container = $this->container();

        return new Authenticator([
            'logger'        => $container['logger'],
            'user_type'     => User::class,
            'user_factory'  => $container['model/factory'],
            'token_type'    => AuthToken::class,
            'token_factory' => $container['model/factory'],
        ]);
    }

    /**
     * Create a new User instance from a given Authenticator.
     *
     * @param  Authenticator $authenticator The authenticator service.
     * @return User
     */
    public function createUser(Authenticator $authenticator)
    {
        $factoryMethod = new ReflectionMethod(\AUTHENTICATOR, 'userFactory');

        return $factoryMethod->invoke($authenticator)->create(User::class);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(Authenticator::class, $this->obj);
    }

    public function testAuthenticate(): void
    {
        $ret = $this->obj->authenticate();
        $this->assertNull($ret);
    }

    public function testAuthenticateByPasswordInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->obj->authenticateByPassword([], '');
    }

    public function testAuthenticateByPasswordInvalidPassword(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->obj->authenticateByPassword('', []);
    }

    public function testAuthenticateByPasswordEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->obj->authenticateByPassword('', '');
    }

    public function testAuthenticateByPassword(): void
    {
        $this->assertNull($this->obj->authenticateByPassword('test', 'password'));
    }

    /**
     * @return void
     */
    /*
    public function testUpdateSession()
    {
        $obj = $this->obj;
    
        $sessionKey = $obj::sessionKey();
        $this->obj['id'] = 'foo';
        $this->obj->saveToSession();
        $this->assertEquals($_SESSION[$sessionKey], $this->obj['id']);
    }
    */
    /**
     * @return void
     */
    /*
    public function testResetPassword()
    {
        $ret = $this->obj->resetPassword('foo');
        $this->assertSame($ret, $this->obj);
    
        $this->obj['id'] = 'bar';
    
        $this->expectException(InvalidArgumentException::class);
        $this->obj->resetPassword(false);
    }
    */
    /**
     * Set up the service container.
     */
    private function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
