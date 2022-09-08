<?php

namespace Charcoal\Tests\Model;

// From 'charcoal-core'
use Charcoal\Model\AbstractModel;
use Charcoal\Model\ModelInterface;
use Charcoal\Model\Model;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ModelTest extends AbstractTestCase
{
    use \Charcoal\Tests\CoreContainerIntegrationTrait;

    /**
     * Tested Class.
     *
     * @var Model
     */
    public $obj;

    /**
     * Retrieve the model's mock metadata.
     *
     * @return array
     */
    private function getModelMetadata()
    {
        return [
            'properties' => [
                'id' => [
                    'type' => 'id'
                ],
                'name' => [
                    'type' => 'string'
                ],
                'role' => [
                    'type' => 'string'
                ]
            ],
            'key' => 'id',
            'sources' => [
                'default' => [
                    'table' => 'charcoal_models'
                ]
            ],
            'default_source' => 'default'
        ];
    }

    /**
     * Create a new model instance.
     *
     * @return ModelInterface
     */
    private function createModel()
    {
        $container = $this->getContainer();

        $obj = $container['model/factory']->create(Model::class);
        $obj->setMetadata($this->getModelMetadata());

        $src = $obj->source();
        $src->setTable('charcoal_models');

        if ($src->tableExists() === false) {
            $src->createTable();
        }

        return $obj;
    }

    /**
     * Drop the SQL table.
     *
     * @return void
     */
    private function dropTable()
    {
        $container = $this->getContainer();

        $container['database']->query('DROP TABLE IF EXISTS `charcoal_models`;');
    }

    /**
     * Retrieve the model's mock object data.
     *
     * @return array
     */
    private function getHuxleyData()
    {
        return [
            'id'   => 1,
            'name' => 'Huxley',
            'role' => 'Novelist'
        ];
    }

    /**
     * Quickly save an object.
     *
     * @return integer The saved object ID.
     */
    private function saveHuxley()
    {
        $obj = $this->obj;
        $obj->setData($this->getHuxleyData());

        return $obj->save();
    }

    /**
     * Set up the test.
     *
     * Create the SQL table for the test, dropping any existing table.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->dropTable();

        $this->obj = $this->createModel();
    }

    /**
     * Tear down the test.
     *
     * Drop any existing SQL table.
     *
     * @return void
     */
    protected function tearDown(): void    {
        $this->dropTable();
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $obj = $this->obj;

        $this->assertInstanceOf(AbstractModel::class, $obj);
        $this->assertInstanceOf(ModelInterface::class, $obj);
    }

    /**
     * @return void
     */
    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([ 'name' => 'Orwell' ]);

        $this->assertSame($ret, $obj);
        $this->assertEquals('Orwell', $obj->name);
    }

    /**
     * @return void
     */
    public function testSetFlatData()
    {
        $obj = $this->obj;
        $ret = $obj->setFlatData([ 'name' => 'Clarke' ]);

        $this->assertSame($ret, $obj);
        $this->assertEquals('Clarke', $obj->name);
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $ret = $this->saveHuxley();

        $this->assertEquals(1, $ret);
    }

    /**
     * @return void
     */
    public function testLoad()
    {
        $ret = $this->saveHuxley();

        $obj1 = $this->createModel();
        $obj1->load(1);

        $this->assertEquals($this->getHuxleyData(), $obj1->data());
    }

    /**
     * @return void
     */
    public function testUpdate()
    {
        $ret = $this->saveHuxley();

        $obj = $this->obj;
        $obj->setData([
            'name' => 'Bradbury',
            'role' => 'Screenwriter'
        ]);
        $ret = $obj->update([ 'name' ]);
        $this->assertTrue($ret);

        $obj1 = $this->createModel();
        $obj1->load(1);

        $this->assertEquals('Bradbury', $obj1['name']);
        $this->assertEquals('Novelist', $obj1['role']);

        $ret = $obj->update();
        $this->assertTrue($ret);

        $obj2 = $this->createModel();
        $obj2->load(1);

        $this->assertEquals('Bradbury', $obj2['name']);
        $this->assertEquals('Screenwriter', $obj2['role']);
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $ret = $this->saveHuxley();

        $obj = $this->obj;
        $ret = $obj->delete();
        $this->assertTrue($ret);

        $obj1 = $this->createModel();
        $obj1->load(1);
        $this->assertEquals(null, $obj1['id']);
    }

    /**
     * @return void
     */
    public function testSerializeUnserialize()
    {
        $obj  = $this->obj;
        $data = $this->getHuxleyData();

        $obj->setData($data);

        $serialized = serialize($obj);
        $this->assertEquals(
            'C:20:"Charcoal\Model\Model":69:{a:3:{s:2:"id";i:1;s:4:"name";s:6:"Huxley";s:4:"role";s:8:"Novelist";}}',
            serialize($obj)
        );

        $obj2 = unserialize($serialized);

        $this->assertInstanceOf(Model::class, $obj2);
        $this->assertEquals(1, $obj2['id']);
        $this->assertEquals('Huxley', $obj2['name']);
    }

    /**
     * @return void
     */
    public function testJsonSerialize()
    {
        $obj  = $this->obj;
        $data = $this->getHuxleyData();

        $obj->setData($data);

        $this->assertEquals(json_encode($data), json_encode($obj));
    }
}
