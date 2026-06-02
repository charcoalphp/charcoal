<?php

declare(strict_types=1);

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
    private \Charcoal\View\Php\PhpEngine $obj;

    public function setUp(): void
    {
        $loader = new PhpLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'templates' ],
        ]);
        $this->obj = new PhpEngine([
            'loader' => $loader,
        ]);
    }

    public function testType(): void
    {
        $this->assertEquals('php', $this->obj->type());
    }

    public function testRender(): void
    {
        $actual = trim($this->obj->render('foo', [ 'foo' => 'Charcoal' ]));
        $this->assertEquals('Hello Charcoal', $actual);
    }

    public function testRenderTemplate(): void
    {
        $actual = trim($this->obj->renderTemplate('Hello <?php echo $foo; ?>  ', [ 'foo' => 'World!' ]));
        $this->assertEquals('Hello World!', $actual);
    }
}
