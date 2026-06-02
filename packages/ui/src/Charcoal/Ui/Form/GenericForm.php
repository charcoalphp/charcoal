<?php

declare(strict_types=1);

namespace Charcoal\Ui\Form;

// From 'charcoal-ui'
use Charcoal\Ui\Form\AbstractForm;

/**
 * A Generic Form
 *
 * Concreete implementation of {@see \Charcoal\Ui\Form\FormInterface}.
 */
class GenericForm extends AbstractForm
{
    /**
     * Retrieve the form type.
     */
    #[\Override]
    public function type(): string
    {
        return 'charcoal/ui/form/generic';
    }
}
