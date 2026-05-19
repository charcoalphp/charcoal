<?php

declare(strict_types=1);

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

    protected function model(): \Charcoal\Model\Model
    {
        $container = $this->getContainer();

        return new Model([
            'container'        => $container,
            'logger'           => $container['logger'],
            'property_factory' => $container['property/factory'],
            'metadata_loader'  => $container['metadata/loader']
        ]);
    }

    public function testConstructor(): void
    {
        $model = $this->model();
        $obj = new ModelValidator($model);
        $this->assertInstanceOf(ModelValidator::class, $obj);
    }

    public function testValidateModel(): void
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
