<?php

namespace Charcoal\Tests\Admin\Script\User;

use DI\Container;
// From 'charcoal-admin'
use Charcoal\Admin\Script\User\ResetPasswordScript;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ResetPasswordScript::class)]
class ResetPasswordScriptTest extends AbstractTestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Instance of class under test
     * @var CreateScript
     */
    private $obj;

    /**
     * @return Container
     */
    private function getContainer()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerScriptDependencies($container);
        return $container;
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->container = $this->getContainer();

        $this->obj = new ResetPasswordScript([
            'logger' => $this->container->get('logger'),
            'climate' => $this->container->get('climate'),
            'model_factory' => $this->container->get('model/factory'),

            // Will call `setDependencies()` on object. AdminScript expects a 'mode/factory'.
            'container' => $this->container
        ]);
    }

    /**
     * @return void
     */
    public function testDefaultArguments()
    {
        $args = $this->obj->defaultArguments();

        $this->assertArrayHasKey('email', $args);
        $this->assertArrayHasKey('password', $args);
    }

    /**
     * @return void
     */
    public function testArguments()
    {
        $args = $this->obj->arguments();

        $this->assertArrayHasKey('email', $args);
        $this->assertArrayHasKey('password', $args);
    }

    /**
     * @return void
     */
    /*
    public function testInvokeWithArguments()
    {
        global $argv;

        $argv = [];
        $argv[] = 'vendor/bin/charcoal';

        $argv[] = '--email';
        $argv[] = 'foobar@example.com';

        $argv[] = '--password';
        $argv[] = '[Foo]{bar}123';

        $model = $this->container->get('model/factory')->create('charcoal/admin/user');
        $source = $model->source();
        $source->createTable();

        $model->setData([
            'email' => 'foobar@example.com',
            'password' => 'BarFoo123'
        ]);
        $model->setRevisionEnabled(false);
        $model->save();

        $request = $this->createMock('\Psr\Http\Message\RequestInterface');
        $response = $this->createMock('\Psr\Http\Message\ResponseInterface');

        $obj = $this->obj;
        $ret = $obj($request, $response);

        $this->assertSame($ret, $response);
    }
    */
}
