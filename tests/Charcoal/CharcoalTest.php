<?php

namespace Charcoal\Tests;

use \Charcoal\Charcoal as Charcoal;

class CharcoalTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Charcoal();
        $this->assertInstanceOf('\Charcoal\Charcoal', $obj);
    }

    /**
    * Hello world
    */
    public function testMergeSimple()
    {
        $obj = new Charcoal();
        
        $arr1 = [1,2,3];
        $arr2 = [3,4,5];

        $merged = Charcoal::merge($arr1, $arr2);
        $this->assertSame($merged, [1,2,3,3,4,5]);
    }

    /**
    * Hello world
    */
    public function testMergeMultiples()
    {
        $arr1 = [1,2,3];
        $arr2 = [3,4,5];
        $arr3 = [5,6,7];
        $arr4 = [7,8,9];

        $merged = Charcoal::merge([], $arr1, $arr2, $arr3, $arr4);
        $this->assertSame($merged, [1,2,3,3,4,5,5,6,7,7,8,9]);
    }

    /**
    * Hello world
    */
    public function testMergeAssoc()
    {
        
        $arr1 = [
            'foo'=>'bar',
            'bar'=>'foo'
        ];
        $arr2 = [
            'foo'=>'baz',
            'baz'=>'con'
        ];

        $merged = Charcoal::merge($arr1, $arr2);
        $this->assertSame($merged, ['foo'=>'baz', 'bar'=>'foo', 'baz'=>'con']);
    }

    public function testMergeAssoc2Dimensions()
    {

        $arr1 = [
            'foo'=>[
                'bar'=>'baz',
                'foo'=>'bar'
            ],
            'bar'=>'foo'
        ];
        $arr2 = [
            'foo'=>[
                'baz'=>'foo'
            ],
            'baz'=>'con'
        ];

        $merged = Charcoal::merge($arr1, $arr2);
        $this->assertSame($merged, [
            'foo'=>[
            'bar'=>'baz',
            'foo'=>'bar',
            'baz'=>'foo'
            ],
            'bar'=>'foo',
            'baz'=>'con'
        ]);
    }

    public function testMergeWithNoParameterThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $merged = Charcoal::merge();
    }

    public function testMergeWithOneParameterThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $merged = Charcoal::merge(['test']);
    }

    public function testMergeWithNonArrayThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $merged = Charcoal::merge(1, 2, 3);
    }

}
