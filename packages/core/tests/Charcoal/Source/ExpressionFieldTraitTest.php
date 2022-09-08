<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\GenericProperty;
use Charcoal\Property\PropertyInterface;

// From 'charcoal-core'
use Charcoal\Source\ExpressionFieldTrait;
use Charcoal\Source\ExpressionFieldInterface;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\CoreContainerIntegrationTrait;

/**
 * Test {@see ExpressionFieldTrait} and {@see ExpressionFieldInterface}.
 */
class ExpressionFieldTraitTest extends AbstractTestCase
{
    use CoreContainerIntegrationTrait;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->getContainerProvider()->registerMultilingualTranslator($container);
    }

    /**
     * Create mock object for testing.
     *
     * @return ExpressionFieldInterface
     */
    final public function createField()
    {
        $obj = $this->getMockForTrait(ExpressionFieldTrait::class);

        return $obj;
    }

    /**
     * Create mock property for testing.
     *
     * @return PropertyInterface
     */
    final public function createProperty()
    {
        $container = $this->getContainer();

        $prop = $container['property/factory']->create('generic');
        $prop->setIdent('xyzzy');

        return $prop;
    }

    /**
     * Test the "property" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts Property
     * 5. Accepts NULL
     *
     * @return void
     */
    public function testProperty()
    {
        $obj = $this->createField();

        /** 1. Default Value */
        $this->assertNull($obj->property());

        /** 2. Mutated Value */
        $that = $obj->setProperty('foobar');
        $this->assertIsString($obj->property());
        $this->assertEquals('foobar', $obj->property());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts {@see PropertyInterface} */
        $property = $this->createProperty();
        $obj->setProperty($property);
        $this->assertInstanceOf(PropertyInterface::class, $obj->property());
        $this->assertSame($property, $obj->property());

        /** 5. Accepts NULL */
        $obj->setProperty(null);
        $this->assertNull($obj->property());
    }

    /**
     * Test the "property" determiner.
     *
     * @return void
     */
    public function testHasProperty()
    {
        $obj = $this->createField();
        $this->assertFalse($obj->hasProperty());

        $obj->setProperty('foobar');
        $this->assertTrue($obj->hasProperty());
    }

    /**
     * Test "property" property with blank value.
     *
     * @return void
     */
    public function testPropertyWithBlankValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createField()->setProperty('');
    }

    /**
     * Test "property" property with invalid property.
     *
     * @return void
     */
    public function testPropertyWithInvalidProperty()
    {
        $container = $this->getContainer();
        $property  = $container['property/factory']->create('generic');

        $this->expectException(InvalidArgumentException::class);
        $this->createField()->setProperty($property);
    }

    /**
     * Test "property" property with invalid value.
     *
     * @return void
     */
    public function testPropertyWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createField()->setProperty([]);
    }

    /**
     * Test the "table" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts NULL
     *
     * @return void
     */
    public function testTable()
    {
        $obj = $this->createField();

        /** 1. Default Value */
        $this->assertNull($obj->table());

        /** 2. Mutated Value */
        $that = $obj->setTable('foobar');
        $this->assertIsString($obj->table());
        $this->assertEquals('foobar', $obj->table());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts NULL */
        $obj->setTable(null);
        $this->assertNull($obj->table());
    }

    /**
     * Test the "table" determiner.
     *
     * @return void
     */
    public function testHasTable()
    {
        $obj = $this->createField();
        $this->assertFalse($obj->hasTable());

        $obj->setTable('foobar');
        $this->assertTrue($obj->hasTable());
    }

    /**
     * Test "table" property with blank value.
     *
     * @return void
     */
    public function testTableWithBlankValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createField()->setTable('');
    }

    /**
     * Test "table" property with invalid value.
     *
     * @return void
     */
    public function testTableWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createField()->setTable([]);
    }

    /**
     * Test the "field_names" method.
     *
     * Assertions:
     * 1. Default state
     * 2. With column name
     * 3. With property instance
     *
     * @return void
     */
    public function testFieldNames()
    {
        $obj = $this->createField();

        /** 1. Default Value */
        $this->assertEquals([], $obj->fieldNames());

        /** 2. With column name */
        $obj->setProperty('foobar');
        $fieldNames = $obj->fieldNames();
        $this->assertIsArray($fieldNames);
        $this->assertContains('foobar', $fieldNames);

        /** 3. With property instance */
        $property = $this->createProperty();
        $obj->setProperty($property);
        $fieldNames = $obj->fieldNames();
        $this->assertContains('xyzzy', $fieldNames);
    }

    /**
     * Test the "field_name" method.
     *
     * Assertions:
     * 1. Default state
     * 2. With column name
     * 3. With property instance
     *
     * @return void
     */
    public function testFieldName()
    {
        $obj = $this->createField();

        /** 1. Default Value */
        $this->assertNull($obj->fieldName());

        /** 2. With column name */
        $obj->setProperty('fooBar');
        $fieldName = $obj->fieldName();
        $this->assertIsString($fieldName);
        $this->assertEquals('foo_bar', $fieldName);

        /** 3. With property instance */
        $property = $this->createProperty();
        $obj->setProperty($property);
        $fieldName = $obj->fieldName();
        $this->assertEquals('xyzzy', $fieldName);
    }

    /**
     * Test the "field_identifiers" method.
     *
     * Assertions:
     * 1. Default state
     * 2. With column name
     * 3. With table name
     *
     * @return void
     */
    public function testFieldIdentifiers()
    {
        $obj = $this->createField();

        /** 1. Default Value */
        $this->assertEquals([], $obj->fieldIdentifiers());

        /** 2. With column name */
        $obj->setProperty('foobar');
        $fieldIdentifiers = $obj->fieldIdentifiers();
        $this->assertIsArray($fieldIdentifiers);
        $this->assertContains('`foobar`', $fieldIdentifiers);

        /** 2. With table name */
        $obj->setTable('bazqux');
        $this->assertContains('bazqux.`foobar`', $obj->fieldIdentifiers());
    }

    /**
     * Test the "field_identifier" method.
     *
     * Assertions:
     * 1. Default state
     * 2. With column name
     * 3. With table name
     *
     * @return void
     */
    public function testFieldIdentifier()
    {
        $obj = $this->createField();

        /** 1. Default Value */
        $this->assertEquals('', $obj->fieldIdentifier());

        /** 2. With column name */
        $obj->setProperty('foobar');
        $fieldIdentifier = $obj->fieldIdentifier();
        $this->assertIsString($fieldIdentifier);
        $this->assertEquals('`foobar`', $fieldIdentifier);

        /** 2. With table name */
        $obj->setTable('bazqux');
        $this->assertEquals('bazqux.`foobar`', $obj->fieldIdentifier());
    }
}
