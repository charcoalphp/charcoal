<?php

namespace Charcoal\Tests\Property;

use PHPUnit_Framework_TestCase;

use PDO;

use Psr\Log\NullLogger;

use Charcoal\Property\LangProperty;

/**
 * Lang Property Test
 */
class LangPropertyTest extends PHPUnit_Framework_TestCase
{
    /**
     * Object under test
     * @var LangProperty
     */
    public $obj;

    public function setUp()
    {
        $this->obj = new LangProperty([
            'database'  => new PDO('sqlite::memory:'),
            'logger'    => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
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

    public function testSqlPdoType()
    {
        $this->assertEquals(PDO::PARAM_BOOL, $this->obj->sqlPdoType());
    }

    public function testChoices()
    {
        //var_dump($this->obj->choices());
    }
}
