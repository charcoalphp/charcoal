<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Model\ModelValidator as ModelValidator;
use \Charcoal\Model\Model as Model;

class ModelValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $model = new Model();
        $obj = new ModelValidator($model);
        $this->assertInstanceOf('\Charcoal\Model\ModelValidator', $obj);
    }

    public function testValidateModel()
    {
        $model = new Model();
        $model->setMetadata(
            [
                'properties' => [
                    'foo' => [
                        'type' => 'string',
                        'required' => true,
                        'min_length' => 5
                    ]
                ]
            ]
        );

        $obj = new ModelValidator($model);
        $ret = $obj->validate();

        // var_dump($ret);
        // $this->assertSame($ret, $obj);
    }
}
