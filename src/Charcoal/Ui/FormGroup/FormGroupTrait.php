<?php

namespace Charcoal\Ui\FormGroup;

use \InvalidArgumentException;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\Form\FormInterface;
use \Charcoal\Ui\FormInput\FormInputBuilder;
use \Charcoal\Ui\FormInput\FormInputInterface;

/**
 *
 */
trait FormGroupTrait
{
    /**
     * @var FormInputInterface[] $inputs
     */
    private $inputs;

    /**
     * In-memory copy of the parent form widget.
     * @var FormInterface $form
     */
    protected $form;

    /**
     * @var FormInputBuilder $formInputBuilder
     */
    protected $formInputBuilder;

    /**
     * The input callback; called on every input.
     * Callable signature: `function(FormInputInterface $input)`
     *
     * @var callable $itemCallback
     */
    private $inputCallback = null;

    /**
     * @var integer $priority
     */
    private $priority;

    /**
     * @var string $l10nMode
     */
    private $l10nMode;

    /**
     * @param FormInputBuilder $builder The builder, to create customized form input objects.
     * @return FormGroupInterface Chainable
     */
    protected function setFormInputBuilder(FormInputBuilder $builder)
    {
        $this->formInputBuilder = $builder;
        return $this;
    }

    /**
     * @param callable $cb The input callback.
     * @return FormGroupInterface Chainable
     */
    public function setInputCallback(callable $cb)
    {
        $this->inputCallback = $cb;
        return $this;
    }

    /**
     * @param FormInterface $form The parent form object.
     * @return FormGroupInterface Chainable
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * Allow the form to public.
     * @return FormInterface
     */
    public function form()
    {
        return $this->form;
    }

    /**
     * @param integer $priority The priority, for ordering purpose.
     * @throws InvalidArgumentException If the priority argument is not a number.
     * @return FormGroupInterface Chainable
     */
    public function setPriority($priority)
    {
        if (!is_numeric($priority)) {
            throw new InvalidArgumentException(
                'Priority must be an integer'
            );
        }
        $priority = (int)$priority;
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return integer
     */
    public function priority()
    {
        return $this->priority;
    }

    /**
     * @param string $mode The l10n mode.
     * @return FormGroupInterface Chainable
     */
    public function setL10nMode($mode)
    {
        $this->l10nMode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function l10nMode()
    {
        return $this->l10nMode;
    }

    /**
     * @param array $inputs The group inputs.
     * @return FormGroupInterface Chainable
     */
    public function setInputs(array $inputs)
    {
        $this->inputs = [];
        foreach ($inputs as $inputIdent => $input) {
            $this->addInput($inputIdent, $input);
        }
        return $this;
    }

    /**
     * @param string                   $inputIdent The input identifier.
     * @param array|FormInputInterface $input      The input object or structure.
     * @throws InvalidArgumentException If the ident argument is not a string or if the input is not valid.
     * @return FormInterface Chainable
     */
    public function addInput($inputIdent, $input)
    {
        if (!is_string($inputIdent)) {
            throw new InvalidArgumentException(
                'Group ident must be a string'
            );
        }

        if (($input instanceof FormInputInterface)) {
            $input->setForm($this->form);
            $input->setFormGroup($this);
            $this->inputs[$inputIdent] = $input;
        } elseif (is_array($input)) {
            $g = $this->formInputBuilder->build($input);
            $this->inputs[$inputIdent] = $g;
        } else {
            throw new InvalidArgumentException(
                'Group must be a Form Group object or an array of form group options'
            );
        }

        return $this;
    }

    /**
     * Form Input generator.
     *
     * @param callable $inputCallback Optional. Input callback.
     * @return FormGroupInterface[]
     */
    public function inputs(callable $inputCallback = null)
    {
        $groups = $this->groups;
        uasort($groups, ['self', 'sortInputsByPriority']);

        $inputCallback = isset($inputCallback) ? $inputCallback : $this->inputCallback;
        foreach ($inputs as $input) {
            if (!$input->l10nMode()) {
                $input->setL10nMode($this->l10nMode());
            }
            if ($inputCallback) {
                $inputCallback($input);
            }
            $GLOBALS['widget_template'] = $input->template();
            yield $input->ident() => $input;
        }

    }

    /**
     * Wether this group contains any inputs.
     *
     * @return boolean
     */
    public function hasInputs()
    {
        return (count($this->inputs) > 0);
    }

    /**
     * Get the number of inputs in this group.
     *
     * @return integer
     */
    public function numInputs()
    {
        return count($this->inputs);
    }
}
