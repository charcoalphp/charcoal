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
     * @param FormGroupBuilder $builder
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
     * @param string                   $groupIdent
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
        } elseif (is_array($group)) {
            $group['form'] = $this;
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
     * @param mixed $a
     * @param mixed $b
     * @return integer Sorting value: -1, 0, or 1
     */
    protected static function sortGroupsByPriority($a, $b)
    {
        $a = $a->priority();
        $b = $b->priority();

        if ($a == $b) {
            // Should be 0?
            return 1;
        }

        return ($a < $b) ? (-1) : 1;
    }
}
