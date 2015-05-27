<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Model\Model as Model;
use \Charcoal\Model\ModelMetadata as Metadata;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Model();
        $this->assertInstanceOf('\Charcoal\Model\Model', $obj);

        //$this->assertEquals([], $obj->properties());
    }

    public function testSetMetadataFromArray()
    {
        $data = [
            'data'=>[
                'foo'=>'bar',
                'bar'=>'foo'
            ]
        ];

        $obj = new Model();
        $obj->set_metadata($data);
        
        $metadata = $obj->metadata();
        $this->assertSame($metadata['data'], $data['data']);
    }

    public function testSetMetadataFromObject()
    {
        $data = [
            'data'=>[
                'foo'=>'bar',
                'bar'=>'foo'
            ]
        ];

        $metadata = new Metadata();
        $metadata->set_data($data);

        $obj = new Model();
        $obj->set_metadata($metadata);
        
        $metadata = $obj->metadata();
        $this->assertSame($metadata['data'], $data['data']);
    }

    public function testSetMetadataSetsData()
    {
        $data = [
            'data'=>[
                'foo'=>'bar',
                'bar'=>'foo'
            ]
        ];

        $obj = new Model();
        $obj->set_metadata($data);
        
        $this->assertEquals($obj->foo, 'bar');
        $this->assertEquals($obj->bar, 'foo');
    }

    public function testSetMetadataSetsProperties()
    {
        $data = [
            'properties'=>[
                'foo'=>[
                    'type'=>'string',
                    'l10n'=>true
                ],
                'bar'=>[
                    'type'=>'boolean'
                ]
            ],
            'data'=>[
                'foo'=>'baz',
                'bar'=>true
            ]
        ];

        $obj = new Model();
        $obj->set_metadata($data);
        
        $properties = $obj->properties();
        //$this->assertEquals(['foo', 'bar'], array_keys($properties));

        // Ensure properties attributes are set
        $foo = $obj->p('foo');
        $bar = $obj->p('bar');
        $this->assertEquals(true, $foo->l10n());

        // Ensure properties data are set
        $this->assertEquals('baz', $obj->foo);
        $this->assertEquals('baz', $foo->val());
        $this->assertEquals(true, $bar->val());
    }

    public function testSetMetadataIsChainable()
    {
        $obj = new Model();
        $ret = $obj->set_metadata([]);

        $this->assertSame($obj, $ret);
    }

    /**
    * @dataProvider invalidMetadataProvider
    */
    public function testSetMetadataInvalidParameterThrowException($invalid_data)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Model();
        $obj->set_metadata($invalid_data);
    }

    public function testSourceWithoutParameterLoadsDefaultSource()
    {
        $obj = new Model();
        $source = $obj->source();
    }

    public function testPropertyWithoutMetadataThrowsException()
    {
        $this->setExpectedException('\Exception');

        $obj = new Model();
        $p = $obj->property('invalid');
    }

    public function testPropertyWithInvalidIdentThrowsException()
    {
        $this->setExpectedException('\Exception');

        $obj = new Model();
        $obj->set_metadata([
            'properties'=>[
                'foo'=>[
                    'type'=>'string'
                ]
            ]
        ]);
        $p = $obj->property('invalid');
    }

    public function testPropertyWithUnspecifiedTypeThrowsException()
    {
        $this->setExpectedException('\Exception');

        $obj = new Model();
        $obj->set_metadata([
            'properties'=>[
                'foo'=>[
                    // no type
                    'l10n'=>true
                ]
            ]
        ]);
        $p = $obj->property('foo');
    }

    public function testPReturnsProperty()
    {
        $obj = new Model();

        $obj->set_metadata([
            'properties'=>[
                'foo'=>[
                    'type'=>'string',
                    'l10n'=>true
                ]
            ],
            'data'=>[
                'foo'=>'baz'
            ]
        ]);

        $p = $obj->p('foo');
        $property = $obj->property('foo');

        $this->assertEquals($p, $property); // todo: assert same
    }

    public function testPWithoutParameterReturnsProperties()
    {
        $obj = new Model();

        $obj->set_metadata([
            'properties'=>[
                'foo'=>[
                    'type'=>'string',
                    'l10n'=>true
                ],
                'bar'=>[
                    'type'=>'boolean'
                ]
            ],
            'data'=>[
                'foo'=>'baz',
                'bar'=>true
            ]
        ]);

        $p = $obj->p();
        $properties = $obj->properties();
        $this->assertEquals($p, $properties); // todo: assert same
    }

    public function testRenderWithEmptyTemplate()
    {
        $obj = new Model();
        $this->assertEquals('', $obj->render(''));
    }

    public function testRenderWithNoReplacements()
    {
        $obj = new Model();
        $this->assertEquals('foo', $obj->render('foo'));
    }

    public function testRender()
    {
        $obj = new Model();
        $obj->set_metadata([
            'properties'=>[
                'foo'=>[
                    'type'=>'string',
                    'l10n'=>true
                ],
                'bar'=>[
                    'type'=>'boolean'
                ]
            ],
            'data'=>[
                'foo'=>'baz',
                'bar'=>true
            ]
        ]);
        $this->assertEquals('foo is baz', $obj->render('foo is {{foo}}'));
    }


    public function testRenderTemplateInvalidTemplateReturnsEmptyString()
    {
        $obj = new Model();
        $obj->render_template('foo');
        $this->assertEquals('', $obj->render_template('foo'));
    }

    public function invalidMetadataProvider()
    {
        $obj = new \StdClass();
        return [
            ['string'],
            [123],
            [null],
            [$obj]
        ];
    }

}

