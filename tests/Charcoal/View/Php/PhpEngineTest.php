<?php

namespace Charcoal\Tests\View\Php;

// From 'charcoal-view'
use Charcoal\View\Php\PhpEngine;
use Charcoal\View\Php\PhpLoader;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class PhpEngineTest extends AbstractTestCase
{
    /**
     * @var MustacheEngine
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $loader = new PhpLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'templates' ],
        ]);
        $this->obj = new PhpEngine([
            'loader' => $loader,
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('php', $this->obj->type());
    }

    /**
     * @return void
     */
    public function testRender()
    {
        $actual = trim($this->obj->render('foo', [ 'foo' => 'Charcoal' ]));
        $this->assertEquals('Hello Charcoal', $actual);
    }

    /**
     * @return void
     */
    public function testRenderTemplate()
    {
        $actual = trim($this->obj->renderTemplate('Hello <?php echo $foo; ?>  ', [ 'foo' => 'World!' ]));
        $this->assertEquals('Hello World!', $actual);
    }
}
