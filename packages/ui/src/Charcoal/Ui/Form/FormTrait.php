<?php

namespace Charcoal\Ui\Form;

use Charcoal\Admin\Widget\FormWidget;
use Charcoal\Ui\ConditionalizableInterface;
use Exception;
use InvalidArgumentException;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal-ui'
use Charcoal\Ui\Form\FormInterface;
use Charcoal\Ui\FormGroup\FormGroupInterface;
use Charcoal\Ui\PrioritizableInterface;

/**
 * Provides an implementation of {@see FormInterface}.
 */
trait FormTrait
{
    /**
     * The URI of a program that processes the form information.
     *
     * @var string
     */
    private $action = '';

    /**
     * The HTTP method that the browser uses to submit the form.
     *
     * @var string
     */
    private $method = 'post';

    /**
     * The form's display mode for multilingual fields.
     *
     * @var string
     */
    private $l10nMode = 'loop_inputs';

    /**
     * The form's display mode for groups.
     *
     * @var string
     */
    protected $groupDisplayMode;

    /**
     * The form's field groups.
     *
     * @var FormGroupInterface[]
     */
    protected $groups = [];

    /**
     * The form's predefined data.
     *
     * @var array $formData
     */
    private $formData = [];

    /**
     * Store the form's metadata instance.
     *
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * Store the form's group factory instance.
     *
     * @var FactoryInterface
     */
    protected $formGroupFactory;

    /**
     * Store the form's group callback.
     *
     * @var callable
     */
    private $groupCallback;

    /**
     * Store the tab ident received through REQUEST.
     *
     * @var string
     */
    private $tabIdent;

    /**
     * Store the current form group.
     *
     * @var string
     */
    private $selectedFormGroup;

    /**
     * Comparison function used by {@see uasort()}.
     *
     * @param  PrioritizableInterface $a Sortable entity A.
     * @param  PrioritizableInterface $b Sortable entity B.
     * @return integer Sorting value: -1 or 1.
     */
    abstract protected function sortItemsByPriority(
        PrioritizableInterface $a,
        PrioritizableInterface $b
    );

    /**
     * @param FactoryInterface $factory A factory, to create customized form gorup objects.
     * @return FormInterface Chainable
     */
    public function setFormGroupFactory(FactoryInterface $factory)
    {
        $this->formGroupFactory = $factory;

        return $this;
    }

    /**
     * @throws Exception If the form group factory object was not set / injected.
     * @return FormInterface Chainable
     */
    protected function formGroupFactory()
    {
        if ($this->formGroupFactory === null) {
            throw new Exception(
                'Form group factory was not set.'
            );
        }

        return $this->formGroupFactory;
    }

    /**
     * @param callable $cb The group callback.
     * @return FormInterface Chainable
     */
    public function setGroupCallback(callable $cb)
    {
        $this->groupCallback = $cb;

        return $this;
    }

    /**
     * @param string $action The "action" value, typically a URL.
     * @throws InvalidArgumentException If the action argument is not a string.
     * @return FormInterface Chainable
     */
    public function setAction($action)
    {
        if (!is_string($action)) {
            throw new InvalidArgumentException(
                'Action must be a string'
            );
        }
        $this->action = $action;

        return $this;
    }

    /**
     * @return string
     */
    public function action()
    {
        return $this->action;
    }

    /**
     * Set the method (forcing lowercase) to "post" or "get".
     *
     * @param string $method Either "post" or "get".
     * @throws InvalidArgumentException If the method is not post or get.
     * @return FormInterface Chainable
     */
    public function setMethod($method)
    {
        $method = strtolower($method);
        if (!in_array($method, ['post', 'get'])) {
            throw new InvalidArgumentException(
                'Method must be "post" or "get"'
            );
        }
        $this->method = $method;

        return $this;
    }

    /**
     * @return string Either "post" or "get".
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * @param string $mode The l10n mode.
     * @return FormInterface Chainable
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
     * Set the object's form groups.
     *
     * @param array $groups A collection of group structures.
     * @return FormInterface Chainable
     */
    public function setGroups(array $groups)
    {
        $this->groups = [];

        foreach ($groups as $groupIdent => $group) {
            $this->addGroup($groupIdent, $group);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function tabIdent()
    {
        return $this->tabIdent;
    }

    /**
     * @param mixed $tabIdent Store the tab ident received through REQUEST.
     * @return self
     */
    public function setTabIdent($tabIdent)
    {
        $this->tabIdent = $tabIdent;

        return $this;
    }

    /**
     * @return string
     */
    public function selectedFormGroup()
    {
        return $this->selectedFormGroup;
    }

    /**
     * @param mixed $selectedFormGroup Store the current form group.
     * @return self
     */
    public function setSelectedFormGroup($selectedFormGroup)
    {
        $this->selectedFormGroup = $selectedFormGroup;

        return $this;
    }

    /**
     * Add a form group.
     *
     * @param  string                   $groupIdent The group identifier.
     * @param  array|FormGroupInterface $group      The group object or structure.
     * @throws InvalidArgumentException If the identifier is not a string or the group is invalid.
     * @return FormInterface Chainable
     */
    public function addGroup($groupIdent, $group)
    {
        if ($group === false || $group === null) {
            return $this;
        }

        $group = $this->parseFormGroup($groupIdent, $group);

        if (isset($group['ident'])) {
            $groupIdent = $group['ident'];
        }

        $this->groups[$groupIdent] = $group;

        return $this;
    }

    /**
     * Parse a form group.
     *
     * @param  string                   $groupIdent The group identifier.
     * @param  array|FormGroupInterface $group      The group object or structure.
     * @throws InvalidArgumentException If the identifier is not a string or the group is invalid.
     * @return FormGroupInterface
     */
    protected function parseFormGroup($groupIdent, $group)
    {
        if (!is_string($groupIdent)) {
            throw new InvalidArgumentException(
                'Group identifier must be a string'
            );
        }

        if ($group instanceof FormGroupInterface) {
            $group = $this->updateFormGroup($group, null, $groupIdent);
        } elseif (is_array($group)) {
            $data = $group;

            if (!isset($data['ident'])) {
                $data['ident'] = $groupIdent;
            }

            $group = $this->createFormGroup($data);
        } else {
            throw new InvalidArgumentException(sprintf(
                'Group must be an instance of %s or an array of form group options, received %s',
                'FormGroupInterface',
                (is_object($group) ? get_class($group) : gettype($group))
            ));
        }

        return $group;
    }

    /**
     * Create a new form group widget.
     *
     * @param  array|null $data Optional. The form group data to set.
     * @return FormGroupInterface
     */
    protected function createFormGroup(array $data = null)
    {
        if (isset($data['type'])) {
            $type = $data['type'];
        } else {
            $type = $this->defaultGroupType();
        }

        $group = $this->formGroupFactory()->create($type);
        $group->setForm($this->formWidget());

        if ($data !== null) {
            $group->setData($data);
        }

        return $group;
    }

    /**
     * Update the given form group widget.
     *
     * @param  FormGroupInterface $group      The form group to update.
     * @param  array|null         $groupData  Optional. The new group data to apply.
     * @param  string|null        $groupIdent Optional. The new group identifier.
     * @return FormGroupInterface
     */
    protected function updateFormGroup(
        FormGroupInterface $group,
        array $groupData = null,
        $groupIdent = null
    ) {
        $group->setForm($this->formWidget());

        if ($groupData !== null) {
            $group->setData($groupData);
        }

        if ($groupIdent !== null) {
            $group->setIdent($groupIdent);
        }

        return $group;
    }

    /**
     * Retrieve the default form group class name.
     *
     * @return string
     */
    public function defaultGroupType()
    {
        return 'charcoal/ui/form-group/generic';
    }

    /**
     * Retrieve the form groups.
     *
     * @param callable $groupCallback Optional callback applied to each form group.
     * @return FormGroupInterface[]|Generator
     */
    public function groups(callable $groupCallback = null)
    {
        $groups = $this->groups;
        uasort($groups, [ $this, 'sortItemsByPriority' ]);

        $groupCallback = (isset($groupCallback) ? $groupCallback : $this->groupCallback);

        $groups = $this->finalizeFormGroups($groups);

        $i = 1;
        foreach ($groups as $group) {
            if ($groupCallback) {
                $groupCallback($group);
            }

            $this->setDynamicTemplate('widget_template', $group->template());

            if (!$this->selectedFormGroup() && $this->isTabbable()) {
                $group->setIsHidden(false);
                if ($i > 1) {
                    $group->setIsHidden(true);
                }
            }
            $i++;

            yield $group;
        }
    }

    /**
     * @param array|FormGroupInterface[] $groups Form groups to finalize.
     * @return array|FormGroupInterface[]
     */
    protected function finalizeFormGroups($groups)
    {
        $out = [];

        foreach ($groups as $group) {
            if (!$group->active()) {
                continue;
            }

            if ($group instanceof ConditionalizableInterface) {
                if (!$group->resolvedCondition()) {
                    continue;
                }
            }

            // Test formGroup vs. ACL roles
            if ($group->isAuthorized() === false) {
                continue;
            }

            if (!$group->l10nMode()) {
                $group->setL10nMode($this->l10nMode());
            }

            if ($this->isTabbable() && $this->tabIdent()) {
                $group->setIsHidden(true);

                if ($group->ident() === $this->tabIdent()) {
                    $group->setIsHidden(false);
                    $this->setSelectedFormGroup($group->ident());
                }
            }

            if ($group->rawConditionalLogic()) {
                if (is_callable([$this, 'obj'])) {
                    foreach ($group->rawConditionalLogic() as $logic) {
                        $valid = true;
                        $value = $this->obj()->get($logic['property']);

                        switch ($logic['operator']) {
                            case '!==':
                            case '!=':
                            case '!':
                            case 'not':
                                if ($value == $logic['value']) {
                                    $valid = false;
                                }
                                break;
                            default:
                            case '"==="':
                            case '"=="':
                            case '"="':
                            case '"is"':
                                if ($value != $logic['value']) {
                                    $valid = false;
                                }
                                break;
                        }

                        if (!$valid) {
                            $group->setConditionalLogicUnmet(true);
                        }
                    }
                }
            }

            $out[] = $group;
        }

        return $out;
    }

    /**
     * Determine if the form has any groups.
     *
     * @return boolean
     */
    public function hasGroups()
    {
        return ($this->numGroups() > 0);
    }

    /**
     * Determine if the form has a given group.
     *
     * @param string $groupIdent The group identifier to look up.
     * @throws InvalidArgumentException If the group identifier is invalid.
     * @return boolean
     */
    public function hasGroup($groupIdent)
    {
        if (!is_string($groupIdent)) {
            throw new InvalidArgumentException(
                'Group identifier must be a string'
            );
        }

        return isset($this->groups[$groupIdent]);
    }

    /**
     * Count the number of form groups.
     *
     * @return integer
     */
    public function numGroups()
    {
        return count($this->groups);
    }

    /**
     * Set the widget's content group display mode.
     *
     * Currently only supports "tab".
     *
     * @param string $mode Group display mode.
     * @throws InvalidArgumentException If the display mode is not a string.
     * @return ObjectFormWidget Chainable.
     */
    public function setGroupDisplayMode($mode)
    {
        if ($mode === null || $mode === '') {
            $this->groupDisplayMode = null;
            return $this;
        }

        if (!is_string($mode)) {
            throw new InvalidArgumentException(
                'Display mode must be a string'
            );
        }

        if ($mode === 'tabs') {
            $mode = 'tab';
        }

        $this->groupDisplayMode = $mode;
        return $this;
    }

    /**
     * Retrieve the widget's content group display mode.
     *
     * @return string|null
     */
    public function groupDisplayMode()
    {
        return $this->groupDisplayMode;
    }

    /**
     * Determine if content groups are to be displayed as tabbable panes.
     *
     * @return boolean
     */
    public function isTabbable()
    {
        return ($this->groupDisplayMode() === 'tab');
    }

    /**
     * @param array $formData The (pre-populated) form data, as [$key=>$val] array.
     * @return FormInterface Chainable
     */
    public function setFormData(array $formData)
    {
        $this->formData = $formData;

        return $this;
    }

    /**
     * @param string $key The form data key, or poperty identifier.
     * @param mixed  $val The form data value, for a given key.
     * @throws InvalidArgumentException If the key argument is not a string.
     * @return FormInterface Chainable
     */
    public function addFormData($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Can not add form data: Data key must be a string'
            );
        }
        $this->formData[$key] = $val;

        return $this;
    }

    /**
     * @return array
     */
    public function formData()
    {
        return $this->formData;
    }

    /**
     * @return FormWidget
     */
    protected function formWidget()
    {
        return $this;
    }
}
