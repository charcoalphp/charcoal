<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\GenericProperty;
use Charcoal\Property\PropertyInterface;

// From 'charcoal-core'
use Charcoal\Source\FieldTrait;
use Charcoal\Source\FieldInterface;
use Charcoal\Tests\ContainerIntegrationTrait;

/**
 *
 */
class FieldTraitTest extends \PHPUnit_Framework_TestCase
{
    use ContainerIntegrationTrait;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->getContainerProvider()->registerMultilingualTranslator($container);
    }

    /**
     * Create mock object for testing.
     *
     * @return FieldInterface
     */
    final public function createField()
    {
        $obj = $this->getMockForTrait(FieldTrait::class);

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
     */
    public function testProperty()
    {
        $obj = $this->createField();

        /** 1. Default Value */
        $this->assertNull($obj->property());

        /** 2. Mutated Value */
        $that = $obj->setProperty('foobar');
        $this->assertInternalType('string', $obj->property());
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
     */
    public function testPropertyWithBlankValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createField()->setProperty('');
    }

    /**
     * Test "property" property with invalid property.
     */
    public function testPropertyWithInvalidProperty()
    {
        $container = $this->getContainer();
        $property  = $container['property/factory']->create('generic');

        $this->setExpectedException(InvalidArgumentException::class);
        $this->createField()->setProperty($property);
    }

    /**
     * Test "property" property with invalid value.
     */
    public function testPropertyWithInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createField()->setProperty([]);
    }

    /**
     * Test the "table_name" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts NULL
     */
    public function testTableName()
    {
        $obj = $this->createField();

        /** 1. Default Value */
        $this->assertNull($obj->tableName());

        /** 2. Mutated Value */
        $that = $obj->setTableName('foobar');
        $this->assertInternalType('string', $obj->tableName());
        $this->assertEquals('foobar', $obj->tableName());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts NULL */
        $obj->setTableName(null);
        $this->assertNull($obj->tableName());
    }

    /**
     * Test the "table_name" determiner.
     */
    public function testHasTableName()
    {
        $obj = $this->createField();
        $this->assertFalse($obj->hasTableName());

        $obj->setTableName('foobar');
        $this->assertTrue($obj->hasTableName());
    }

    /**
     * Test "table_name" property with blank value.
     */
    public function testTableNameWithBlankValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createField()->setTableName('');
    }

    /**
     * Test "table_name" property with invalid value.
     */
    public function testTableNameWithInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createField()->setTableName([]);
    }

    /**
     * Test the "field_names" method.
     *
     * Assertions:
     * 1. Default state
     * 2. With column name
     * 3. With property instance
     */
    public function testFieldNames()
    {
        $obj = $this->createField();

        /** 1. Default Value */
        $this->assertEquals([], $obj->fieldNames());

        /** 2. With column name */
        $obj->setProperty('foobar');
        $fieldNames = $obj->fieldNames();
        $this->assertInternalType('array', $fieldNames);
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
     */
    public function testFieldName()
    {
        $obj = $this->createField();

        /** 1. Default Value */
        $this->assertNull($obj->fieldName());

        /** 2. With column name */
        $obj->setProperty('foobar');
        $fieldName = $obj->fieldName();
        $this->assertInternalType('string', $fieldName);
        $this->assertEquals('foobar', $fieldName);

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
     */
    public function testFieldIdentifiers()
    {
        $obj = $this->createField();

        /** 1. Default Value */
        $this->assertEquals([], $obj->fieldIdentifiers());

        /** 2. With column name */
        $obj->setProperty('foobar');
        $fieldIdentifiers = $obj->fieldIdentifiers();
        $this->assertInternalType('array', $fieldIdentifiers);
        $this->assertContains('`foobar`', $fieldIdentifiers);

        /** 2. With table name */
        $obj->setTableName('bazqux');
        $this->assertContains('bazqux.`foobar`', $obj->fieldIdentifiers());
    }

    /**
     * Test the "field_identifier" method.
     *
     * Assertions:
     * 1. Default state
     * 2. With column name
     * 3. With table name
     */
    public function testFieldIdentifier()
    {
        $obj = $this->createField();

        /** 1. Default Value */
        $this->assertEquals('', $obj->fieldIdentifier());

        /** 2. With column name */
        $obj->setProperty('foobar');
        $fieldIdentifier = $obj->fieldIdentifier();
        $this->assertInternalType('string', $fieldIdentifier);
        $this->assertEquals('`foobar`', $fieldIdentifier);

        /** 2. With table name */
        $obj->setTableName('bazqux');
        $this->assertEquals('bazqux.`foobar`', $obj->fieldIdentifier());
    }
}
