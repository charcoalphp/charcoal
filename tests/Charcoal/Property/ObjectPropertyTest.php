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
use Charcoal\Tests\Property\Mocks\GenericModel;

/**
 *
 */
class ObjectPropertyTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

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

        $objs = [
            self::OBJ_1 => [ 'active' => 1, 'position' => 1, 'name' => $translator->translation([ 'en' => 'Foo', 'fr' => 'Oof' ]) ],
            self::OBJ_2 => [ 'active' => 0, 'position' => 2, 'name' => $translator->translation([ 'en' => '',    'fr' => '' ]),   ],
            self::OBJ_3 => [ 'active' => 0, 'position' => 3, 'name' => $translator->translation([ 'en' => 'Baz', 'fr' => 'Zab' ]) ],
            self::OBJ_4 => [ 'active' => 1, 'position' => 4, 'name' => $translator->translation([ 'en' => 'Qux', 'fr' => 'Xuq' ]) ],
            self::OBJ_5 => [ 'active' => 1, 'position' => 4, 'name' => $translator->translation([ 'en' => 'Xyz', 'fr' => 'Zyx' ]) ],
        ];

        $models = [];
        foreach ($objs as $objId => $objData) {
            $models[$objId] = $container['model/factory']->create(GenericModel::class);
            $models[$objId]->setId($objId)->setData($objData)->save();
        }

        return $objs;
    }

    public static function getMethod($obj, $name)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public static function callMethod($obj, $name, array $args = [])
    {
        $method = static::getMethod($obj, $name);

        return $method->invokeArgs($obj, $args);
    }

    /**
     * @dataProvider provideMissingDependencies
     */
    public function testConstructorWithoutDependencies($method, $expectedException)
    {
        $container = $this->getContainer();

        $prop = new ObjectProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);

        $this->setExpectedException($expectedException);
        $this->callMethod($prop, $method);
    }

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
     */
    public function testConstructorWithDependencies($method, $expected)
    {
        $container = $this->getContainer();

        $dependency = $this->callMethod($this->obj, $method);
        $this->assertInstanceOf($expected, $dependency);
    }

    public function provideSatisfiedDependencies()
    {
        return [
            [ 'modelFactory',     FactoryInterface::class ],
            [ 'collectionLoader', CollectionLoader::class ],
            [ 'cachePool',        CacheItemPoolInterface::class ],
        ];
    }

    public function testType()
    {
        $this->assertEquals('object', $this->obj->type());
    }

    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    public function testSqlType()
    {
        $this->obj->setObjType(GenericModel::class);
        $this->assertEquals('CHAR(13)', $this->obj->sqlType());

        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    public function testSqlPdoType()
    {
        $this->obj->setObjType(GenericModel::class);
        $this->assertEquals(PDO::PARAM_STR, $this->obj->sqlPdoType());
    }

    public function testSetObjType()
    {
        $return = $this->obj->setObjType('foo');
        $this->assertSame($return, $this->obj);
        $this->assertEquals('foo', $this->obj->objType());

        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj->setObjType(false);
    }

    public function testAccessingObjTypeBeforeSetterThrowsException()
    {
        $this->setExpectedException('\Exception');
        $this->obj->objType();
    }

    public function testSetPattern()
    {
        $return = $this->obj->setPattern('{{foo}}');
        $this->assertSame($return, $this->obj);
        $this->assertEquals('{{foo}}', $this->obj->pattern());

        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj->setPattern([]);
    }

    public function testParseOneWithScalarValue()
    {
        $this->assertEquals('foobar', $this->obj->parseOne('foobar'));

        $mock = $this->getMock(StorableInterface::class);
        $this->assertNull($this->obj->parseOne($mock));

        // Force ID to 'foo'.
        $mock->expects($this->any())
             ->method('id')
             ->will($this->returnValue('foo'));
        $this->assertEquals('foo', $this->obj->parseOne($mock));
    }

    public function testParseOneWithObjectWithoutIdReturnsNull()
    {
        $mock = $this->getMock(StorableInterface::class);
        $this->assertNull($this->obj->parseOne($mock));
    }

    public function testParseOneWithObjectWithIdReturnsId()
    {
        $mock = $this->getMock(StorableInterface::class);
        $mock->expects($this->any())
             ->method('id')
             ->will($this->returnValue('foo'));
        $this->assertEquals('foo', $this->obj->parseOne($mock));
    }

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

        $this->assertEquals('Foo, ' . self::OBJ_2 . ', Baz, Qux, Xyz', $this->obj->displayVal(implode(',', array_keys($objs))));
        $this->assertEquals('Foo, Baz, Qux', $this->obj->displayVal([ $models[self::OBJ_1], self::OBJ_3, $models[self::OBJ_4] ]));
    }

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

    public function testRenderObjPatternThrowsExceptionWithBadPattern()
    {
        $container = $this->getContainer();

        $model = $container['model/factory']->create(GenericModel::class);

        $this->setExpectedException(InvalidArgumentException::class);
        $return = $this->callMethod($this->obj, 'renderObjPattern', [ $model, false ]);
    }

    public function testRenderObjPatternThrowsExceptionWithBadLang()
    {
        $container = $this->getContainer();

        $model = $container['model/factory']->create(GenericModel::class);

        $this->setExpectedException(InvalidArgumentException::class);
        $return = $this->callMethod($this->obj, 'renderObjPattern', [ $model, null, false ]);
    }

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

    public function testChoiceLabelStructException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj->choiceLabel([]);
    }

    public function testParseChoicesThrowsException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $return = $this->callMethod($this->obj, 'parseChoices', [ false ]);
    }

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

    public function testModelLoader()
    {
        $objs = $this->setUpObjects();

        $this->obj->setObjType(GenericModel::class);

        $return = $this->callMethod($this->obj, 'modelLoader');
        $this->assertInstanceOf(ModelLoader::class, $return);
    }

    public function testModelLoaderThrowsException()
    {
        $this->obj->setObjType(GenericModel::class);

        $this->setExpectedException(InvalidArgumentException::class);
        $return = $this->callMethod($this->obj, 'modelLoader', [ false ]);
    }
}
