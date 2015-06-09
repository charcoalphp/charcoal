<?php

namespace Charcoal\Tests\Source;

use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Source\DatabaseSource as DatabaseSource;

use \Charcoal\Model\Object as Object;

class StorableTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;
    public $source;

    static public function setUpBeforeClass()
    {
        //include 'DatabaseTestModel.php';

        Charcoal::config()->add_database('unit_test', [
            'username'=>'root',
            'password'=>'',
            'database'=>'charcoal_examples'
        ]);

        Charcoal::config()->set_default_database('unit_test');

        $obj = new DatabaseSource();
        //$obj->set_model($model);
        $obj->set_table('test');
        $q = 'DROP TABLE IF EXISTS `test`';
        $obj->db()->query($q);
    }

    public function setUp()
    {
        $mock_obj = $this->getMockForTrait('\Charcoal\Source\StorableTrait');
        $mock_source = $this->getMockForAbstractClass('\Charcoal\Source\AbstractSource');

        $this->obj = $mock_obj;//new ViewableClass();
        $this->source = $mock_source;
    }

    public function testSetStorableData()
    {
        $obj =  $this->obj;
        $source = $this->source;

        $ret = $obj->set_storable_data([
            'source'=>$source
        ]);
        $this->assertSame($obj, $ret);
        $this->assertSame($source, $obj->source());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_storable_data(false);
    }

    public function testSetSource()
    {
        $obj =  $this->obj;
        $source = $this->source;

        $ret = $obj->set_source($source);
        $this->assertSame($obj, $ret);
        $this->assertSame($source, $obj->source());
    }

    protected function getItemModel()
    {
        $model = new Object();

        $model->set_metadata([
            'properties'=>[
                'id'=>[
                    'type'=>'id',

                ],
                'name'=>[
                    'type'=>'string',
                    'max_length'=>300
                ]
            ],
            'sources'=>[
                'default'=>'test'
            ]
        ]);
       
        $s = new DatabaseSource();
        $s->set_model($model);
        $s->set_table('test');

        $model->set_source($s);
        $model->source()->create_table();

        return $model;
    }

    /**
    * Test StorabletTrait::save() through the Object class, which uses StorableTrait
    */
    public function testSave()
    {
        $model = $this->getItemModel();
        $model->set_data([
            'id'=>1,
            'name'=>'Foo bar.baz'
        ]);
        //$ret = $model->save();
        //var_dump($ret);
    }

    public function testLoad()
    {
        $model = $this->getItemModel();
        //$ret = $model->load(1);
        //var_dump($ret);
    }
}
