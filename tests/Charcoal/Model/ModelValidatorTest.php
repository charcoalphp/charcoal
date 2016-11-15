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
    private $obj;
    private $model;

    protected function model()
    {
        $logger = new \Psr\Log\NullLogger();
        $cache  = new \Cache\Adapter\Void\VoidCachePool();

        $metadataLoader = new \Charcoal\Model\Service\MetadataLoader([
            'logger'    => $logger,
            'cache'     => $cache,
            'base_path' => __DIR__,
            'paths'     => [ 'metadata' ]
        ]);

        $propertyFactory = new \Charcoal\Factory\GenericFactory([
            'base_class'       => \Charcoal\Property\PropertyInterface::class,
            'default_class'    => \Charcoal\Property\GenericProperty::class,
            'resolver_options' => [
                'prefix' => '\Charcoal\Property\\',
                'suffix' => 'Property'
            ]
        ]);

        $dependencies = [
            'logger'           => $logger,
            'database'         => new PDO('sqlite::memory:'),
            'property_factory' => $propertyFactory,
            'metadata_loader'  => $metadataLoader
        ];

        $propertyFactory->setArguments($dependencies);

        return new Model($dependencies);
    }

    public function testConstructor()
    {
        $model = $this->model();
        $obj = new ModelValidator($model);
        $this->assertInstanceOf('\Charcoal\Model\ModelValidator', $obj);
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
