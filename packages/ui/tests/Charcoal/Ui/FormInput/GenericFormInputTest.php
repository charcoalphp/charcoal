<?php

namespace Charcoal\Tests\Ui\FormInput;

// From 'charcoal-ui'
use Charcoal\Ui\FormInput\GenericFormInput;
use Charcoal\Ui\ServiceProvider\FormServiceProvider;
use Charcoal\Ui\ServiceProvider\LayoutServiceProvider;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\View\AbstractView;

/**
 *
 */
class GenericFormInputTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var AbstractView $obj
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

        $container['view'] = null;

        $this->obj = new GenericFormInput([
            'logger'             => $container['logger'],
            'layout_builder'     => $container['layout/builder'],
            'form_group_factory' => $container['form/group/factory']
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
