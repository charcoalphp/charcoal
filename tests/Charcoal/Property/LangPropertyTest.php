<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\LangProperty;

/**
 * Lang Property Test
 */
class LangPropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object under test
     * @var LangProperty
     */
    public $obj;

    public function setUp()
    {
        $this->obj = new LangProperty();
    }

    public function testType()
    {
        $this->assertEquals('lang', $this->obj->type());
    }

    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    public function testSqlType()
    {
        $this->assertEquals('CHAR(2)', $this->obj->sqlType());
        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
        $this->obj->setMultiple(false);
        $this->assertEquals('CHAR(2)', $this->obj->sqlType());
    }

    public function testChoices()
    {
        //var_dump($this->obj->choices());
    }
}
