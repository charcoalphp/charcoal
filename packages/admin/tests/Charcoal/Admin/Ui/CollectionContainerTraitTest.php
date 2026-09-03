<?php

namespace Charcoal\Tests\Admin\Ui;

use ReflectionMethod;

use Charcoal\Admin\Ui\CollectionContainerTrait;
use Charcoal\Loader\CollectionLoader;

use PHPUnit\Framework\TestCase;

/**
 * Request collection_config must not keep raw SQL conditions.
 */
class CollectionContainerTraitTest extends TestCase
{
    /**
     * @return object
     */
    private function createContainer()
    {
        return new class {
            use CollectionContainerTrait;
        };
    }

    /**
     * @param  object  $container Trait host.
     * @param  string  $method    Protected method name.
     * @param  mixed   ...$args   Arguments.
     * @return mixed
     */
    private function callProtected($container, $method, ...$args)
    {
        $ref = new ReflectionMethod($container, $method);
        $ref->setAccessible(true);

        return $ref->invoke($container, ...$args);
    }

    /**
     * Untrusted parse strips condition / string / bare SQL; keeps predicates.
     *
     * @return void
     */
    public function testParseCollectionConfigSanitizesUntrustedFiltersAndOrders()
    {
        $out = $this->callProtected($this->createContainer(), 'parseCollectionConfig', [
            'filters' => [
                [
                    'property' => 'name',
                    'operator' => '=',
                    'value'    => "' OR '1'='1",
                ],
                [
                    'condition' => '1=1 OR 1=1',
                ],
                'raw SQL string',
            ],
            'orders' => [
                [
                    'property'  => 'id',
                    'direction' => 'asc',
                ],
                [
                    'mode'      => 'custom',
                    'condition' => 'RAND()',
                ],
            ],
        ], false);

        $this->assertCount(1, $out['filters']);
        $this->assertSame('name', $out['filters'][0]['property']);
        $this->assertSame("' OR '1'='1", $out['filters'][0]['value']);
        $this->assertArrayNotHasKey('condition', $out['filters'][0]);

        $this->assertCount(1, $out['orders']);
        $this->assertSame('id', $out['orders'][0]['property']);
        $this->assertArrayNotHasKey('condition', $out['orders'][0]);
    }

    /**
     * Trusted parse keeps developer-authored raw conditions.
     *
     * @return void
     */
    public function testParseCollectionConfigKeepsTrustedConditions()
    {
        $out = $this->callProtected($this->createContainer(), 'parseCollectionConfig', [
            'filters' => [
                [ 'condition' => 'NOW() > `expiry`' ],
            ],
        ], true);

        $this->assertSame('NOW() > `expiry`', $out['filters'][0]['condition']);
    }

    /**
     * Loader receives filters/orders via setFilters/setOrders, not setData.
     *
     * @return void
     */
    public function testApplyCollectionConfigToLoaderPassesTrustedFlag()
    {
        $filters = [
            [ 'property' => 'active', 'value' => true ],
        ];
        $orders = [
            [ 'property' => 'id', 'direction' => 'desc' ],
        ];

        $loader = $this->getMockBuilder(CollectionLoader::class)
            ->disableOriginalConstructor()
            ->onlyMethods([ 'setData', 'setFilters', 'setOrders' ])
            ->getMock();

        $loader->expects($this->once())
            ->method('setData')
            ->with($this->equalTo([ 'pagination' => [ 'num_per_page' => 20 ] ]));

        $loader->expects($this->once())
            ->method('setFilters')
            ->with($this->equalTo($filters), false);

        $loader->expects($this->once())
            ->method('setOrders')
            ->with($this->equalTo($orders), false);

        $this->callProtected($this->createContainer(), 'applyCollectionConfigToLoader', $loader, [
            'pagination' => [ 'num_per_page' => 20 ],
            'filters'    => $filters,
            'orders'     => $orders,
        ], false);
    }
}
