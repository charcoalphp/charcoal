<?php

namespace Charcoal\Tests\Admin\Property\Input;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-ui'
use Charcoal\Ui\Form\FormInterface;
use Charcoal\Ui\FormGroup\FormGroupInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\NestedWidgetInput;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 * Test the nested widget form field.
 */
class NestedWidgetInputTest extends AbstractTestCase
{
    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * @return void
     */
    public function testFormReturnsNullWhenViewControllerDefaultsToInput()
    {
        $input = $this->createInput();

        $this->assertSame($input, $input->viewController());
        $this->assertNull($input->exposeForm());
    }

    /**
     * @return void
     */
    public function testFormReturnsFormGroupForm()
    {
        $form = $this->createMock(FormInterface::class);

        $formGroup = $this->createMock(FormGroupInterface::class);
        $formGroup->method('form')->willReturn($form);

        $input = $this->createInput();
        $input->setFormGroup($formGroup);

        $this->assertSame($form, $input->exposeForm());
    }

    /**
     * @return void
     */
    public function testFormReturnsViewControllerForm()
    {
        $form  = $this->createMock(FormInterface::class);
        $input = $this->createInput();
        $input->setViewController($form);

        $this->assertSame($form, $input->exposeForm());
    }

    /**
     * @return NestedWidgetInput
     */
    private function createInput()
    {
        $container = $this->container();

        return new class ([
            'logger'          => $container['logger'],
            'metadata_loader' => $container['metadata/loader'],
            'container'       => $container,
        ]) extends NestedWidgetInput {
            /**
             * Expose the protected form lookup for testing.
             *
             * @return FormInterface|null
             */
            public function exposeForm()
            {
                return $this->form();
            }
        };
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
            $containerProvider->registerInputDependencies($container);

            $container['widget/factory'] = function () {
                return new Factory();
            };

            $container['form/group/factory'] = function () {
                return new Factory();
            };

            $this->container = $container;
        }

        return $this->container;
    }
}
