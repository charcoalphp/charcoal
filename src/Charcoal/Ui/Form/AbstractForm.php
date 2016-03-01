<?php

namespace Charcoal\Ui\Form;

use \InvalidArgumentException;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\AbstractUiItem;
use \Charcoal\Ui\Form\FormInterface;
use \Charcoal\Ui\FormGroup\FormGroupBuilder;
use \Charcoal\Ui\Layout\LayoutAwareInterface;
use \Charcoal\Ui\Layout\LayoutAwareTrait;

/**
 *
 */
abstract class AbstractForm extends AbstractUiItem implements
    FormInterface,
    LayoutAwareInterface
{
    use LayoutAwareTrait;

    /**
     * @var string $action
     */
    private $action = '';

    /**
     * @var string $method
     */
    private $method = 'post';

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
     * @param array|ArrayAccess $data The class dependencies.
     */
    public function __construct($data = null)
    {
        $this->setFormGroupBuilder($data['form_group_builder']);

        // Set up layout builder (to fulfill LayoutAware Interface)
        $this->setLayoutBuilder($data['layout_builder']);
    }

    /**
     * @param FormGroupBuilder $builder
     * @return FormInterface Chainable
     */
    protected function setFormGroupBuilder(FormGroupBuilder $builder)
    {
        $this->formGroupBuilder = $builder;
        return $this;
    }

    /**
     * @param callable $cb
     * @return FormInterface Chainable
     */
    public function setGroupCallback(callable $cb)
    {
        $this->groupCallback = $cb;
        return $this;
    }

    /**
     * @param string $action
     * @throws InvalidArgumentException
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
     * @param string $method Either "post" or "get"
     * @throws InvalidArgumentException
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
     * @param array $groups
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
     * @param string $groupIdent
     * @param array|FormGroupInterface
     * @throws InvalidArgumentException
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
        } else if (is_array($group)) {
            $group['form'] = $this;
            $g = $this->formGroupBuilder->build($group);
            $this->groups[$groupIdent] = $g;
        } else {
            throw new InvalidArgumentException(
                'Group must be a Form Group object or an array of form group options'
            );
        }

        return $this;
    }

    /**
     * Form Group generator.
     *
     * @return FormGroupInterface[]
     */
    public function groups(callable $groupCallback = null)
    {
        $groups = $this->groups;
        uasort($groups, ['self', 'sortGroupsByPriority']);

        $groupCallback = isset($groupCallback) ? $groupCallback : $this->groupCallback;
        foreach ($groups as $group) {
            if ($groupCallback) {
                $groupCallback($group);
            }
            $GLOBALS['widget_template'] = $group->widgetType();
            yield $group->ident() => $group;
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
     * @param array $formData
     * @return FormInterface Chainable
     */
    public function setFormData(array $formData)
    {
        $this->formData = $formData;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $val
     * @throws InvalidArgumentException
     * @return FormInterface Chainable
     */
    public function addFormData($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Key must be a string'
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
}
