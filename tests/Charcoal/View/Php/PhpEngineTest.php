<?php

namespace Charcoal\Tests\View\Php;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\View\Php\PhpEngine;
use Charcoal\View\Php\PhpLoader;

/**
 *
 */
class PhpEngineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MustacheEngine
     */
    private $obj;

    public function setUp()
    {
        $logger = new NullLogger();
        $loader = new PhpLoader([
            'logger'    => $logger,
            'base_path' => __DIR__,
            'paths'     => ['templates']
        ]);
        $this->obj = new PhpEngine([
            'logger' => $logger,
            'loader' => $loader
        ]);
    }

    public function testType()
    {
        $this->assertEquals('php', $this->obj->type());
    }

    public function testRender()
    {
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', ['foo'=>'Charcoal'])));
    }

    public function testRenderTemplate()
    {
        $this->assertEquals('Hello World!', trim($this->obj->renderTemplate('Hello <?php echo $foo; ?>  ', ['foo' => 'World!'])));
    }
}
