<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-property'
use Charcoal\Property\GenericProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class GenericPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var GenericProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new GenericProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('generic', $this->obj->type());
    }

    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    public function testSqlType()
    {
        $this->assertEquals('VARCHAR(255)', $this->obj->sqlType());
        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    public function testSqlPdoType()
    {
        $this->assertEquals(\PDO::PARAM_STR, $this->obj->sqlPdoType());
    }
}
