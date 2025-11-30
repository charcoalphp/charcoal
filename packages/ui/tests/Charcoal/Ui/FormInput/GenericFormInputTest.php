<?php

namespace Charcoal\Tests\Ui;

use Charcoal\Ui\FormInput\GenericFormInput;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GenericFormInput::class)]
class GenericFormInputTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var AbstractViewClass $obj
     */
    public $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();
        (new FormServiceProvider())->register($container);
        (new LayoutServiceProvider())->register($container);

        $container->set('view', null);

        $this->obj = new GenericFormInput([
            'logger'             => $container->get('logger'),
            'layout_builder'     => $container->get('layout/builder'),
            'form_group_factory' => $container->get('form/group/factory')
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('charcoal/ui/form-input/generic', $this->obj->type());
    }
}
