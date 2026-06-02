<?php

declare(strict_types=1);

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

    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = new GenericProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    public function testType(): void
    {
        $this->assertEquals('generic', $this->obj->type());
    }

    public function testSqlExtra(): void
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    public function testSqlType(): void
    {
        $this->assertEquals('VARCHAR(255)', $this->obj->sqlType());
        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    public function testSqlPdoType(): void
    {
        $this->assertEquals(\PDO::PARAM_STR, $this->obj->sqlPdoType());
    }
}
