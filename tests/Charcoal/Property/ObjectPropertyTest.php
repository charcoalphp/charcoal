<?php

namespace Charcoal\Tests\Property;

use PDO;
use ReflectionClass;
use RuntimeException;
use InvalidArgumentException;

// From PSR-6
use Psr\Cache\CacheItemPoolInterface;

// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
use Charcoal\Model\Service\ModelLoader;
use Charcoal\Source\StorableInterface;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-property'
use Charcoal\Property\ObjectProperty;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Property\ContainerIntegrationTrait;
use Charcoal\Tests\Property\Mocks\GenericModel;

/**
 *
 */
class ObjectPropertyTest extends AbstractTestCase
{
    use ReflectionsTrait;
    use ContainerIntegrationTrait;

    const OBJ_1 = '40ea';
    const OBJ_2 = '69c6';
    const OBJ_3 = '71b5';
    const OBJ_4 = 'dce3';
    const OBJ_5 = 'ea9f';

    /**
     * Tested Class.
     *
     * @var ObjectProperty
     */
    public $obj;

    /**
     * Set up the test.
     *
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->getContainerProvider()->registerMultilingualTranslator($container);

        $this->obj = new ObjectProperty([
            'container'  => $container,
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    /**
     * Set up models for the test.
     *
     * @param  GenericModel[]|null $models If provided, it is filled with the
     *     collection of models for testing.
     * @return array Returns the collection of object data for testing.
     */
    public function setUpObjects(&$models = null)
    {
        $container  = $this->getContainer();
        $translator = $container['translator'];
        $factory    = $container['model/factory'];
        $prototype  = $factory->get(GenericModel::class);
        $source     = $prototype->source();

        if (!$source->tableExists()) {
            $source->createTable();
        }

        // phpcs:disable Generic.Files.LineLength.TooLong
        $objs = [
            self::OBJ_1 => [ 'active' => 1, 'position' => 1, 'name' => $translator->translation([ 'en' => 'Foo', 'fr' => 'Oof' ]) ],
            self::OBJ_2 => [ 'active' => 0, 'position' => 2, 'name' => $translator->translation([ 'en' => '',    'fr' => '' ]),   ],
            self::OBJ_3 => [ 'active' => 0, 'position' => 3, 'name' => $translator->translation([ 'en' => 'Baz', 'fr' => 'Zab' ]) ],
            self::OBJ_4 => [ 'active' => 1, 'position' => 4, 'name' => $translator->translation([ 'en' => 'Qux', 'fr' => 'Xuq' ]) ],
            self::OBJ_5 => [ 'active' => 1, 'position' => 4, 'name' => $translator->translation([ 'en' => 'Xyz', 'fr' => 'Zyx' ]) ],
        ];
        // phpcs:enable

        $models = [];
        foreach ($objs as $objId => $objData) {
            $models[$objId] = $container['model/factory']->create(GenericModel::class);
            $models[$objId]->setId($objId)->setData($objData)->save();
        }

        return $objs;
    }

    /**
     * @dataProvider provideMissingDependencies
     *
     * @param  string $method            The name of a method accessor.
     * @param  string $expectedException The expected Exception thrown by $method.
     * @return void
     */
    public function testConstructorWithoutDependencies($method, $expectedException)
    {
        $container = $this->getContainer();

        $prop = new ObjectProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);

        $this->expectException($expectedException);
        $this->callMethod($prop, $method);
    }

    /**
     * @return array
     */
    public function provideMissingDependencies()
    {
        return [
            [ 'modelFactory',     RuntimeException::class ],
            [ 'collectionLoader', RuntimeException::class ],
            [ 'cachePool',        RuntimeException::class ],
        ];
    }

    /**
     * @dataProvider provideSatisfiedDependencies
     *
     * @param  string $method         The name of a method accessor.
     * @param  string $expectedObject The expected instance returned by $method.
     * @return void
     */
    public function testConstructorWithDependencies($method, $expectedObject)
    {
        $container = $this->getContainer();

        $dependency = $this->callMethod($this->obj, $method);
        $this->assertInstanceOf($expectedObject, $dependency);
    }

    /**
     * @return array
     */
    public function provideSatisfiedDependencies()
    {
        return [
            [ 'modelFactory',     FactoryInterface::class ],
            [ 'collectionLoader', CollectionLoader::class ],
            [ 'cachePool',        CacheItemPoolInterface::class ],
        ];
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('object', $this->obj->type());
    }

    /**
     * @return void
     */
    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    /**
     * @return void
     */
    public function testSqlType()
    {
        $this->obj->setObjType(GenericModel::class);
        $this->assertEquals('CHAR(13)', $this->obj->sqlType());

        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    /**
     * @return void
     */
    public function testSqlPdoType()
    {
        $this->obj->setObjType(GenericModel::class);
        $this->assertEquals(PDO::PARAM_STR, $this->obj->sqlPdoType());
    }

    /**
     * @return void
     */
    public function testSetObjType()
    {
        $return = $this->obj->setObjType('foo');
        $this->assertSame($return, $this->obj);
        $this->assertEquals('foo', $this->obj['objType']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setObjType(false);
    }

    /**
     * @return void
     */
    public function testAccessingObjTypeBeforeSetterThrowsException()
    {
        $this->expectException('\Exception');
        $this->obj['objType'];
    }

    /**
     * @return void
     */
    public function testSetPattern()
    {
        $return = $this->obj->setPattern('{{foo}}');
        $this->assertSame($return, $this->obj);
        $this->assertEquals('{{foo}}', $this->obj['pattern']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setPattern([]);
    }

    /**
     * @return void
     */
    public function testParseOneWithScalarValue()
    {
        $this->assertEquals('foobar', $this->obj->parseOne('foobar'));

        $mock = $this->createMock(StorableInterface::class);
        $this->assertNull($this->obj->parseOne($mock));

        // Force ID to 'foo'.
        $mock->expects($this->any())
             ->method('id')
             ->will($this->returnValue('foo'));
        $this->assertEquals('foo', $this->obj->parseOne($mock));
    }

    /**
     * @return void
     */
    public function testParseOneWithObjectWithoutIdReturnsNull()
    {
        $mock = $this->createMock(StorableInterface::class);
        $this->assertNull($this->obj->parseOne($mock));
    }

    /**
     * @return void
     */
    public function testParseOneWithObjectWithIdReturnsId()
    {
        $mock = $this->createMock(StorableInterface::class);
        $mock->expects($this->any())
             ->method('id')
             ->will($this->returnValue('foo'));
        $this->assertEquals('foo', $this->obj->parseOne($mock));
    }

    /**
     * @return void
     */
    public function testDisplayVal()
    {
        $objs  = $this->setUpObjects($models);
        $first = $models[self::OBJ_1];
        $third = $models[self::OBJ_3];

        $this->obj->setObjType(GenericModel::class);

        $container  = $this->getContainer();
        $translator = $container['translator'];

        $val = [
            'en' => self::OBJ_1,
            'fr' => self::OBJ_3
        ];
        $l10n = $translator->translation($val);

        $this->obj->setL10n(false);

        $this->assertEquals('', $this->obj->displayVal(null));
        $this->assertEquals('', $this->obj->displayVal(''));

        $this->assertEquals($first['name']['en'], $this->obj->displayVal($first));
        $this->assertEquals(self::OBJ_2, $this->obj->displayVal($models[self::OBJ_2]));
        $this->assertEquals($first['name']['en'], $this->obj->displayVal($l10n));
        $this->assertEquals($first['name']['en'], $this->obj->displayVal($l10n, [ 'pattern' => 'name' ]));

        $this->obj->setL10n(true);

        $this->assertEquals('', $this->obj->displayVal($val['en']));
        $this->assertEquals('', $this->obj->displayVal($val, [ 'lang' => 'es' ]));
        $this->assertEquals($first['name']['en'], $this->obj->displayVal($l10n));
        $this->assertEquals($first['name']['en'], $this->obj->displayVal($val));
        $this->assertEquals($third['name']['fr'], $this->obj->displayVal($val, [ 'lang' => 'fr' ]));

        $this->obj->setL10n(false);
        $this->obj->setMultiple(true);

        $expected = 'Foo, '.self::OBJ_2.', Baz, Qux, Xyz';
        $actual   = $this->obj->displayVal(implode(',', array_keys($objs)));
        $this->assertEquals($expected, $actual);

        $expected = 'Foo, Baz, Qux';
        $actual   = $this->obj->displayVal([ $models[self::OBJ_1], self::OBJ_3, $models[self::OBJ_4] ]);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return void
     */
    public function testInputVal()
    {
        $container = $this->getContainer();

        $this->assertEquals('', $this->obj->inputVal(''));
        $this->assertEquals('', $this->obj->inputVal(null));

        $this->assertEquals('foo', $this->obj->inputVal('foo'));
        $this->assertEquals('["foo","baz","qux"]', $this->obj->inputVal([ 'foo', 'baz', 'qux' ]));

        $model = $container['model/factory']->create(GenericModel::class);
        $model->setId(self::OBJ_1);
        $this->assertEquals(self::OBJ_1, $this->obj->inputVal($model));

        $this->obj->setMultiple(true);
        $this->assertEquals('foo,baz,qux', $this->obj->inputVal('foo,baz,qux'));
        $this->assertEquals('foo,baz,qux', $this->obj->inputVal([ 'foo', 'baz', 'qux' ]));
    }

    /**
     * @return void
     */
    public function testStorageVal()
    {
        $container = $this->getContainer();

        $this->assertNull($this->obj->storageVal(''));
        $this->assertNull($this->obj->storageVal(null));

        $this->assertEquals('foo', $this->obj->storageVal('foo'));
        $this->assertEquals('["foo","baz","qux"]', $this->obj->storageVal([ 'foo', 'baz', 'qux' ]));

        $model = $container['model/factory']->create(GenericModel::class);
        $model->setId(self::OBJ_1);
        $this->assertEquals(self::OBJ_1, $this->obj->storageVal($model));

        $this->obj->setMultiple(true);
        $this->assertEquals('foo,baz,qux', $this->obj->storageVal('foo,baz,qux'));
        $this->assertEquals('foo,baz,qux', $this->obj->storageVal([ 'foo', 'baz', 'qux' ]));
    }

    /**
     * @return void
     */
    public function testRenderObjPattern()
    {
        $objs = $this->setUpObjects($models);

        $return = $this->callMethod($this->obj, 'renderObjPattern', [ $models[self::OBJ_1], '' ]);
        $this->assertEmpty($return);

        $return = $this->callMethod($this->obj, 'renderObjPattern', [ $models[self::OBJ_1], 'name' ]);
        $this->assertEquals($models[self::OBJ_1]['name']['en'], $return);

        $return = $this->callMethod($this->obj, 'renderObjPattern', [ $models[self::OBJ_1], 'name', 'fr' ]);
        $this->assertEquals($models[self::OBJ_1]['name']['fr'], $return);

        /** Test RegExp pattern renderer */
        $return = $this->callMethod($this->obj, 'renderObjPattern', [ $models[self::OBJ_1] ]);
        $this->assertEquals($models[self::OBJ_1]['name']['en'], $return);
    }

    /**
     * @return void
     */
    public function testRenderViewableObjPattern()
    {
        $container = $this->getContainer();
        $this->getContainerProvider()->registerView($container);

        $factory = $container['model/factory'];

        $depends = $factory->arguments();
        $depends[0]['view'] = $container['view'];

        $factory->setArguments($depends);

        $objs = $this->setUpObjects($models);

        /** Test 'charcoal-view' renderer */
        $return = $this->callMethod($this->obj, 'renderObjPattern', [ $models[self::OBJ_1] ]);
        $this->assertEquals($models[self::OBJ_1]['name']['en'], $return);
    }

    /**
     * @return void
     */
    public function testRenderObjPatternThrowsExceptionWithBadPattern()
    {
        $container = $this->getContainer();

        $model = $container['model/factory']->create(GenericModel::class);

        $this->expectException(InvalidArgumentException::class);
        $return = $this->callMethod($this->obj, 'renderObjPattern', [ $model, false ]);
    }

    /**
     * @return void
     */
    public function testRenderObjPatternThrowsExceptionWithBadLang()
    {
        $container = $this->getContainer();

        $model = $container['model/factory']->create(GenericModel::class);

        $this->expectException(InvalidArgumentException::class);
        $return = $this->callMethod($this->obj, 'renderObjPattern', [ $model, null, false ]);
    }

    /**
     * @return void
     */
    public function testChoices()
    {
        $this->obj->setObjType(GenericModel::class);

        /** Database table does not exist */
        $this->assertFalse($this->obj->hasChoices());
        $this->assertEmpty($this->obj->choices());

        /** Database table created */
        $objs = $this->setUpObjects($models);

        /** Test available choices */
        $this->assertTrue($this->obj->hasChoices());
        $this->assertTrue($this->obj->hasChoice(self::OBJ_1));
        $this->assertFalse($this->obj->hasChoice(uniqid()));

        $expected = $this->callMethod($this->obj, 'parseChoice', [ $models[self::OBJ_1] ]);
        $this->assertEquals($expected, $this->obj->choice(self::OBJ_1));

        $this->assertNull($this->obj->choice(uniqid()));

        $choices = $this->obj->choices();
        $this->assertEquals(array_keys($models), array_keys($choices));

        /** Test immutability of choices */
        $this->obj->addChoice('foo', 'foo');
        $this->obj->addChoices([ 'baz' => 'baz' ]);
        $this->obj->setChoices([ 'qux' => 'qux' ]);
        $this->assertEquals(array_keys($models), array_keys($choices));

        /** Test label retrieval */
        $this->assertNull($this->obj->choiceLabel(null));

        $this->assertEquals('foo', $this->obj->choiceLabel([ 'label' => 'foo' ]));
        $this->assertEquals('foo', $this->obj->choiceLabel([ 'value' => 'foo' ]));
        $this->assertEquals($models[self::OBJ_1]['name'], $this->obj->choiceLabel($models[self::OBJ_1]));

        $fakeId = uniqid();
        $this->assertEquals($fakeId, $this->obj->choiceLabel($fakeId));
    }

    /**
     * @return void
     */
    public function testChoiceLabelStructException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->choiceLabel([]);
    }

    /**
     * @return void
     */
    public function testParseChoicesThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $return = $this->callMethod($this->obj, 'parseChoices', [ false ]);
    }

    /**
     * @return void
     */
    public function testCollectionLoading()
    {
        $this->setUpObjects();

        $this->obj->setObjType(GenericModel::class);

        $this->obj->setFilters([
            [ 'property' => 'active', 'value' => true ]
        ]);

        $this->obj->setOrders([
            [ 'property' => 'position', 'mode' => 'DESC' ]
        ]);

        $this->obj->setPagination([
            'num_per_page' => 3
        ]);

        $loader = $this->callMethod($this->obj, 'collectionModelLoader');
    }

    /**
     * @return void
     */
    public function testLoadObject()
    {
        $container = $this->getContainer();

        $objs = $this->setUpObjects();

        $this->obj->setObjType(GenericModel::class);

        $expected = $container['model/factory']->create(GenericModel::class);
        $expected->setId(self::OBJ_1)->setData($objs[self::OBJ_1]);

        $return = $this->callMethod($this->obj, 'loadObject', [ $expected ]);
        $this->assertSame($expected, $return);

        $return = $this->callMethod($this->obj, 'loadObject', [ self::OBJ_1 ]);
        $this->assertEquals($expected->data(), $return->data());

        $return = $this->callMethod($this->obj, 'loadObject', [ uniqid() ]);
        $this->assertNull($return);
    }

    /**
     * @return void
     */
    public function testModelLoader()
    {
        $objs = $this->setUpObjects();

        $this->obj->setObjType(GenericModel::class);

        $return = $this->callMethod($this->obj, 'modelLoader');
        $this->assertInstanceOf(ModelLoader::class, $return);
    }

    /**
     * @return void
     */
    public function testModelLoaderThrowsException()
    {
        $this->obj->setObjType(GenericModel::class);

        $this->expectException(InvalidArgumentException::class);
        $return = $this->callMethod($this->obj, 'modelLoader', [ false ]);
    }
}
