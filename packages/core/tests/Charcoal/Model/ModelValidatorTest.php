<?php

namespace Charcoal\Tests\Model;

use PDO;

// From 'charcoal-core'
use Charcoal\Model\ModelValidator;
use Charcoal\Model\Model;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ModelValidatorTest extends AbstractTestCase
{
    use \Charcoal\Tests\CoreContainerIntegrationTrait;

    /**
     * @return Model
     */
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

    /**
     * @return void
     */
    public function testConstructor()
    {
        $model = $this->model();
        $obj = new ModelValidator($model);
        $this->assertInstanceOf(ModelValidator::class, $obj);
    }

    /**
     * @return void
     */
    public function testValidateModel()
    {
        $model = $this->model();
        $model->setMetadata([
            'properties' => [
                'foo' => [
                    'type'       => 'string',
                    'required'   => true,
                    'min_length' => 5
                ]
            ]
        ]);

        $validator = new ModelValidator($model);
        $this->assertFalse($validator->validate());

        $model['foo'] = 'qux';
        $this->assertFalse($validator->validate());

        $model['foo'] = 'xyzzy';
        $this->assertTrue($validator->validate());
    }
}
