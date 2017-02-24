<?php

namespace Charcoal\Tests\Model;

use \PDO;

use \Charcoal\Model\ModelValidator;
use \Charcoal\Model\Model;

/**
 *
 */
class ModelValidatorTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\ContainerIntegrationTrait;

    private $obj;
    private $model;

    protected function model()
    {
        $container = $this->getContainer();

        return new Model([
            'container'        => $container,
            'logger'           => $container['logger'],
            'property_factory' => $container['property/factory'],
            'metadata_loader'  => $container['metadata/loader']
        ]);
    }

    public function testConstructor()
    {
        $model = $this->model();
        $obj = new ModelValidator($model);
        $this->assertInstanceOf(ModelValidator::class, $obj);
    }

    public function testValidateModel()
    {
        $model = $this->model();
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
