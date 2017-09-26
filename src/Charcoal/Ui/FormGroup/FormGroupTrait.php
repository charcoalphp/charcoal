<?php

namespace Charcoal\Ui\FormGroup;

use InvalidArgumentException;

// Intra-module (`charcoal-ui`) dependencies
use Charcoal\Ui\Form\FormInterface;
use Charcoal\Ui\FormInput\FormInputBuilder;
use Charcoal\Ui\FormInput\FormInputInterface;

/**
 * Provides an implementation of {@see \Charcoal\Ui\FormGroup\FormGroupInterface}.
 */
trait FormGroupTrait
{
    /**
     * Store a reference to the parent form widget.
     *
     * @var FormInterface
     */
    protected $form;

    /**
     * The group's collection of fields.
     *
     * @var FormInputInterface[]
     */
    private $inputs;

    /**
     * The input callback; called on every input.
     *
     * Callable signature: `function(FormInputInterface $input)`
     *
     * @var callable
     */
    private $inputCallback;

    /**
     * Store the builder instance for the current class.
     *
     * @var FormInputBuilder
     */
    protected $formInputBuilder;

    /**
     * The L10N display mode.
     *
     * @var string
     */
    private $l10nMode;

    /**
     * The group's identifier.
     *
     * @var string
     */
    private $ident;

    /**
     * The required Acl permissions fetch from form group.
     *
     * @var string[] $requiredAclPermissions
     */
    private $requiredAclPermissions = [];

    /**
     * Class or Classes for tab form group.
     *
     * @var string|string[]
     */
    private $tabCssClasses;

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
     * @return FormInterface
     */
    public function form()
    {
        return $this->form;
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
     * @return FormGroupInterface Chainable
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
            $g                         = $this->formInputBuilder->build($input);
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
     * @return FormGroupInterface[]|Generator
     */
    public function inputs(callable $inputCallback = null)
    {
        $groups = $this->groups;
        uasort($groups, ['self', 'sortItemsByPriority']);

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
            $GLOBALS['widget_template'] = '';
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

    /**
     * Set the identifier of the group.
     *
     * @param string $ident The group identifier.
     * @return self
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * Retrieve the idenfitier of the group.
     *
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * @param string|\string[] $classes Class or Classes for tab form group.
     * @return self
     */
    public function setTabCssClasses($classes)
    {
        if (is_string($classes)) {
            $this->tabCssClasses = $classes;
        }

        if (is_array($classes)) {
            $this->tabCssClasses = implode(' ', $classes);
        }

        return $this;
    }

    /**
     * @return string|\string[]
     */
    public function tabCssClasses()
    {
        return $this->tabCssClasses;
    }
}
