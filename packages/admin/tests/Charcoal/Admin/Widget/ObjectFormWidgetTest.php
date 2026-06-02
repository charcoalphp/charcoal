<?php

namespace Charcoal\Tests\Admin\Widget;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\ObjectFormWidget;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ObjectFormWidgetTest extends AbstractTestCase
{
    /**
     * @var ObjectFormWidget
     */
    public $obj;

    public function setUp(): void
    {
        $logger = new NullLogger();
        $this->obj = new ObjectFormWidget([
            'logger' => $logger
        ]);
    }

    public function testSetFormIdent(): void
    {
        $ret = $this->obj->setFormIdent('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->formIdent());

        if (class_exists('\Throwable', false)) {
            $this->expectException('\Throwable');
        } else {
            $this->expectException('\Exception');
        }

        $this->obj->setFormIdent(false);
    }
}
