<?php

namespace Charcoal\Tests\Admin\Script\User;

use PDO;

// From PSR-3
use Psr\Log\NullLogger;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Source\DatabaseSource;

// From 'charcoal-admin'
use Charcoal\Admin\Script\User\CreateScript;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class CreateScriptTest extends AbstractTestCase
{
    private \Pimple\Container $container;

    /**
     * Instance of class under test
     */
    private \Charcoal\Admin\Script\User\CreateScript $obj;

    private function getContainer(): \Pimple\Container
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerScriptDependencies($container);
        return $container;
    }

    public function setUp(): void
    {
        $this->container = $this->getContainer();

        $this->obj = new CreateScript([
            'logger'        => $this->container['logger'],
            'climate'       => $this->container['climate'],
            'model_factory' => $this->container['model/factory'],
            'container'     => $this->container,
        ]);
    }

    public function testDefaultArguments(): void
    {
        $args = $this->obj->defaultArguments();

        $this->assertArrayHasKey('email', $args);
        $this->assertArrayHasKey('password', $args);
        $this->assertArrayHasKey('roles', $args);
    }

    public function testArguments(): void
    {
        $args = $this->obj->arguments();

        $this->assertArrayHasKey('email', $args);
        $this->assertArrayHasKey('password', $args);
        $this->assertArrayHasKey('roles', $args);
    }

    // public function testInvoke()
    // {
    //     // Ensure that no admin user exists in test database
    //     $this->assertEquals(0, $this->numAdminUsersInSource());

    //     $request = $this->createMock('\Psr\Http\Message\RequestInterface');
    //     $response = $this->createMock('\Psr\Http\Message\ResponseInterface');

    //     $obj = $this->obj;
    //     $ret = $obj($request, $response);

    //     $this->assertSame($ret, $response);

    //     // Ensure one user was created in database
    //     $this->assertEquals(1, $this->numAdminUsersInSource());
    // }

    // public function testInvokeWithArguments()
    // {
    //     global $argv;

    //     $argv = [];
    //     $argv[] = 'vendor/bin/charcoal';

    //     $argv[] = '-e';
    //     $argv[] = 'foo@example.com';

    //     $argv[] = '-p';
    //     $argv[] = '[Foo]{bar}123';

    //     $argv[] = '-r';
    //     $argv[] = 'admin';

    //     // Ensure that no admin user exists in test database
    //     $this->assertEquals(0, $this->numAdminUsersInSource());

    //     $request = $this->createMock('\Psr\Http\Message\RequestInterface');
    //     $response = $this->createMock('\Psr\Http\Message\ResponseInterface');

    //     $obj = $this->obj;
    //     $ret = $obj($request, $response);

    //     $this->assertSame($ret, $response);

    //     // Ensure one user was created in database
    //     $this->assertEquals(1, $this->numAdminUsersInSource());

    //     $created = $this->container['model/factory']->create('charcoal/admin/user')->load('foo');
    //     $this->assertEquals('foo@example.com', $created['email']);
    //     $this->assertEquals(['admin'], $created['roles']);
    // }

    // public function testRun()
    // {
    //     // Ensure that no admin user exists in test database
    //     $this->assertEquals(0, $this->numAdminUsersInSource());

    //     $request = $this->createMock('\Psr\Http\Message\RequestInterface');
    //     $response = $this->createMock('\Psr\Http\Message\ResponseInterface');

    //     $ret = $this->obj->run($request, $response);

    //     $this->assertSame($ret, $response);

    //     // Ensure one user was created in database
    //     $this->assertEquals(1, $this->numAdminUsersInSource());
    // }
}
