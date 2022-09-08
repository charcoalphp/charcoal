<?php

namespace Charcoal\Tests\Property;

use ReflectionMethod;

// From 'charcoal-property'
use Charcoal\Property\GenericProperty;
use Charcoal\Property\PropertyField;
use Charcoal\Property\StorablePropertyInterface;
use Charcoal\Property\StorablePropertyTrait;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Property\ContainerIntegrationTrait;

/**
 *
 */
class StorablePropertyTraitTest extends AbstractTestCase
{
    use ReflectionsTrait;
    use ContainerIntegrationTrait;

    /**
     * @var StorablePropertyInterfaceß
     */
    private $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->getContainerProvider()->registerMultilingualTranslator($container);

        $this->obj = $this->createProperty();
    }

    /**
     * @return GenericProperty
     */
    public function createProperty()
    {
        $container = $this->getContainer();

        $prop = $container['property/factory']->create(GenericProperty::class);

        $prop['ident'] = 'test';
        return $prop;
    }

    /**
     * @return StorablePropertyTrait
     */
    public function createMultilingualProperty()
    {
        $prop = $this->createProperty();

        $prop['l10n'] = true;
        return $prop;
    }

    /**
     * @return StorablePropertyTrait
     */
    public function createMultiValueProperty()
    {
        $prop = $this->createProperty();

        $prop['multiple'] = true;
        return $prop;
    }

    /**
     * @return void
     */
    public function testStorageVal()
    {
        $container = $this->getContainer();

        $val = $container['translator']->translation([ 'en' => 'Cooking', 'fr' => 'Cuisson' ]);
        $ret = $this->obj->storageVal($val);
        $this->assertEquals('Cooking', $ret);

        $obj = $this->createMultiValueProperty();

        $val = [ 'foo', 'baz', 'qux' ];
        $ret = $obj->storageVal($val);
        $this->assertEquals('foo,baz,qux', $ret);

        $val = 'xyzzy';
        $ret = $obj->storageVal($val);
        $this->assertEquals('xyzzy', $ret);
    }

    /**
     * Test Unilingual Property Fields
     *
     * @return void
     */
    public function testFields()
    {
        $fields = $this->obj->fields('Cooking');

        $this->assertIsArray($fields);
        $this->assertCount(1, $fields);

        $field = reset($fields);

        $this->assertInstanceOf(PropertyField::class, $field);
        $this->assertEquals('test', $field->ident());
        $this->assertEquals('Cooking', $field->val());

        $fields = $this->obj->fields([]);
        $field  = reset($fields);
        $this->assertEquals('[]', $field->val());
    }

    /**
     * @return void
     */
    public function testUpdateFields()
    {
        $fields = $this->callMethod($this->obj, 'updatedFields', [ [], 'Cooking' ]);
        $this->assertEquals(['Cooking'], array_map(fn($field) => $field->val(), array_values($fields)));
    }

    /**
     * Test Multilingual Property Fields
     *
     * @return void
     */
    public function testMultilingualFields()
    {
        $container = $this->getContainer();

        $obj = $this->createMultilingualProperty();

        $fields = $obj->fields('Cooking');

        $this->assertIsArray($fields);
        $this->assertCount(4, $fields);
        $this->assertInstanceOf(PropertyField::class, $fields['en']);
        $this->assertEquals('test_en', $fields['en']->ident());
        $this->assertEquals('Cooking', $fields['en']->val());

        $fields = $obj->fields([ 'en' => 'Cooking', 'fr' => 'Cuisson' ]);

        $this->assertInstanceOf(PropertyField::class, $fields['fr']);
        $this->assertEquals('test_fr', $fields['fr']->ident());
        $this->assertEquals('Cuisson', $fields['fr']->val());

        $this->assertInstanceOf(PropertyField::class, $fields['de']);
        $this->assertEquals('test_de', $fields['de']->ident());
        $this->assertEquals(null, $fields['de']->val());

        $this->assertInstanceOf(PropertyField::class, $fields['es']);
        $this->assertEquals('test_es', $fields['es']->ident());
        $this->assertEquals(null, $fields['es']->val());
    }

    /**
     * Test Unilingual Property Field Names
     *
     * @return void
     */
    public function testFieldNames()
    {
        $names = $this->obj->fieldNames();

        $this->assertIsArray($names);
        $this->assertCount(1, $names);

        $name = reset($names);

        $this->assertIsString($name);
        $this->assertEquals('test', $name);
    }

    /**
     * Test Multilingual Property Field Names
     *
     * @return void
     */
    public function testMultilingualFieldNames()
    {
        $obj = $this->createMultilingualProperty();

        $names = $obj->fieldNames();

        $this->assertIsArray($names);
        $this->assertCount(4, $names);

        $this->assertIsString($names['en']);
        $this->assertEquals('test_en', $names['en']);

        $this->assertIsString($names['fr']);
        $this->assertEquals('test_fr', $names['fr']);

        $this->assertIsString($names['de']);
        $this->assertEquals('test_de', $names['de']);

        $this->assertIsString($names['es']);
        $this->assertEquals('test_es', $names['es']);
    }
}
