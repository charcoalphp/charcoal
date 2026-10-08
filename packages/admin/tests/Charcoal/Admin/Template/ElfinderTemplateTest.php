<?php

namespace Charcoal\Tests\Admin\Template;

use ReflectionClass;

// From Pimple
use Pimple\Container;

// From 'charcoal-property'
use Charcoal\Property\FileProperty;
use Charcoal\Property\PropertyInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Template\ElfinderTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class ElfinderTemplateTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var ElfinderTemplate
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     *
     * @return void
     */
    public function setUp(): void
    {
        $container = $this->container();

        $this->obj = new ElfinderTemplate([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    /**
     * @return void
     */
    public function testAdminAssertsUrl()
    {
        $ret = $this->obj->adminAssetsUrl();
        $this->assertEquals('/assets/admin/', $ret);
    }

    /**
     * @return void
     */
    public function testFilePropertyDoesNotRememberLastDirByDefault()
    {
        $this->setFormProperty($this->obj, $this->createMock(FileProperty::class));

        $settings = $this->obj->elfinderClientConfig();
        $this->assertFalse($settings['rememberLastDir']);
    }

    /**
     * @return void
     */
    public function testNonFilePropertyRemembersLastDirByDefault()
    {
        $this->setFormProperty($this->obj, $this->createMock(PropertyInterface::class));

        $settings = $this->obj->elfinderClientConfig();
        $this->assertTrue($settings['rememberLastDir']);
    }

    /**
     * @return void
     */
    public function testFilePropertyRespectsConfiguredRememberLastDir()
    {
        // Use a fresh container; the one from setUp() has frozen its services.
        $this->container = null;

        $container = $this->container();
        $container['elfinder/config'] = [
            'client' => [
                'rememberLastDir' => true,
            ],
        ];

        $obj = new ElfinderTemplate([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
        $this->setFormProperty($obj, $this->createMock(FileProperty::class));

        $settings = $obj->elfinderClientConfig();
        $this->assertTrue($settings['rememberLastDir']);
    }

    /**
     * @param  ElfinderTemplate  $obj      The template to alter.
     * @param  PropertyInterface $property The form property to assign.
     * @return void
     */
    private function setFormProperty(ElfinderTemplate $obj, PropertyInterface $property)
    {
        $reflection = new ReflectionClass($obj);
        $prop = $reflection->getProperty('formProperty');
        $prop->setAccessible(true);
        $prop->setValue($obj, $property);
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    protected function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerTemplateDependencies($container);
            $containerProvider->registerElfinderConfig($container);
            $container['widget/factory'] = $this->createMock('\Charcoal\Factory\FactoryInterface');

            $this->container = $container;
        }

        return $this->container;
    }
}
