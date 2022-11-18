<?php

namespace Charcoal\Tests\Ui\Form;

// From 'charcoal-ui'
use Charcoal\Ui\Form\GenericForm;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\View\AbstractView;

/**
 *
 */
class GenericFormTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var AbstractView
     */
    public $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();
        $container->register(new FormServiceProvider());
        $container->register(new LayoutServiceProvider());

        $this->obj = new GenericForm([
            'logger'             => $container['logger'],
            'view'               => $container['view'],
            'layout_builder'     => $container['layout/builder'],
            'form_group_factory' => $container['form/group/factory'],
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('charcoal/ui/form/generic', $this->obj->type());
    }
}
