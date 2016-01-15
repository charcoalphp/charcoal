<?php

namespace Charcoal\Tests\View\Mustache;

use \Charcoal\View\Mustache\GenericHelper;

class GenericHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MustacheEngine
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new GenericHelper;
    }

    public function testAddJs()
    {
        $this->obj->addJs('foo');
        $this->assertEquals('foo', $this->obj->js());
    }
}
