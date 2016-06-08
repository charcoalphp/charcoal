<?php

namespace Charcoal\Ui\Form;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\FormGroup\FormGroupBuilder;
use \Charcoal\Ui\FormGroup\FormGroupInterface;

/**
 *
 */
trait FormTrait
{
    /**
     * @var string $action
     */
    private $action = '';

    /**
     * @var string $method
     */
    private $method = 'post';

    /**
     * @var string $l10nMode
     */
    private $l10nMode = 'loop_inputs';

    /**
     * @var FormGroupInterface[] $groups
     */
    protected $groups = [];

    /**
     * @var array $formData
     */
    private $formData = [];

    /**
     * @var MetadataInterface $metadata
     */
    private $metadata = null;

    /**
     * @var FormGroupBuilder $formGroupBuilder
     */
    protected $formGroupBuilder = null;

    /**
     * @var callable $groupCallback
     */
    private $groupCallback = null;

    /**
     * @param FormGroupBuilder $builder A builder, to create customized form gorup objects.
     * @return FormInterface Chainable
     */
    public function setFormGroupBuilder(FormGroupBuilder $builder)
    {
        $this->formGroupBuilder = $builder;
        return $this;
    }

    /**
     * @throws Exception If the form group builder object was not set / injected.
     * @return FormGroupBuilder
     */
    protected function formGroupBuilder()
    {
        if ($this->formGroupBuilder === null) {
            throw new Exception(
                'Form group builder was not set.'
            );
        }
        return $this->formGroupBuilder;
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
     * @param array $groups The groups structure.
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
     * @param string                   $groupIdent The group identifier.
     * @param array|FormGroupInterface $group      The group object or structure.
     * @throws InvalidArgumentException If the ident is not a string or the group not a valid object or structure.
     * @return FormInterface Chainable
     */
    public function addGroup($groupIdent, $group)
    {
        if (!is_string($groupIdent)) {
            throw new InvalidArgumentException(
                'Group ident must be a string'
            );
        }

        if (($group instanceof FormGroupInterface)) {
            $group->setForm($this);
            $this->groups[$groupIdent] = $group;
        } elseif (is_array($group)) {
            $group['form'] = $this;
            if (!isset($group['type'])) {
                $group['type'] = $this->defaultGroupType();
            }
            $g = $this->formGroupBuilder()->build($group);
            $this->groups[$groupIdent] = $g;
        } else {
            throw new InvalidArgumentException(
                'Group must be a Form Group object or an array of form group options'
            );
        }

        return $this;
    }

    /**
     * @return string
     */
    public function defaultGroupType()
    {
        return 'charcoal/ui/form-group/generic';
    }

    /**
     * Form Group generator.
     *
     * @param callable $groupCallback Optional. Group callback.
     * @return FormGroupInterface[]
     */
    public function groups(callable $groupCallback = null)
    {
        $groups = $this->groups;
        uasort($groups, ['self', 'sortGroupsByPriority']);

        $groupCallback = isset($groupCallback) ? $groupCallback : $this->groupCallback;
        foreach ($groups as $group) {
            if (!$group->active()) {
                continue;
            }

            if (!$group->l10nMode()) {
                $group->setL10nMode($this->l10nMode());
            }

            if ($groupCallback) {
                $groupCallback($group);
            }

            $GLOBALS['widget_template'] = $group->template();

            yield $group;
        }

    }

    /**
     * @return boolean
     */
    public function hasGroups()
    {
        return (count($this->groups) > 0);
    }

    /**
     * @return integer
     */
    public function numGroups()
    {
        return count($this->groups);
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
     * To be called with uasort()
     *
     * @param FormGroupInterface $a First group object to sort.
     * @param FormGroupInterface $b Second group object to sort.
     * @return integer Sorting value: -1 or 1
     */
    protected static function sortGroupsByPriority(FormGroupInterface $a, FormGroupInterface $b)
    {
        $a = $a->priority();
        $b = $b->priority();

        return ($a < $b) ? (-1) : 1;
    }
}
