<?php

namespace Charcoal\Admin\Property\Input;

use DomainException;
// From 'charcoal-app'
use Charcoal\App\Template\WidgetInterface;
// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\NestedWidgetInput;
use Charcoal\Admin\Widget\FormGroup\StructureFormGroup;

/**
 * Structure Widget Form Field
 */
class StructureWidgetInput extends NestedWidgetInput
{
    /**
     * Create the nested widget.
     *
     * @throws DomainException If the widget is not a structure widget.
     * @return WidgetInterface
     */
    #[\Override]
    protected function createWidget()
    {
        $widget = parent::createWidget();

        if ($widget instanceof StructureFormGroup) {
            $widget->setStorageProperty($this->property());
        } else {
            throw new DomainException(sprintf(
                'Widget must an instance of %s, received %s',
                StructureFormGroup::class,
                $widget::class
            ));
        }

        return $widget;
    }

    /**
     * Retrieve the default structure widget options.
     */
    #[\Override]
    public function defaultWidgetData(): array
    {
        return [
            'type' => StructureFormGroup::class
        ];
    }
}
