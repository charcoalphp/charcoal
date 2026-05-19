<?php

namespace Charcoal\Ui;

use InvalidArgumentException;
// From 'charcoal-view'
use Charcoal\View\ViewableInterface;
use Charcoal\View\ViewInterface;

/**
 * Provides an entity with a condition.
 *
 * Implementation of {@see \Charcoal\Ui\ConditionalizableInterface}
 */
trait ConditionalizableTrait
{
    /**
     * The condition needed to render the entity.
     *
     * @var string|boolean
     */
    private $condition;

    /**
     * The resolved condition.
     *
     * @var string|boolean
     */
    private $resolvedCondition;

    /**
     * @return boolean
     */
    public function resolvedCondition()
    {
        if (!isset($this->resolvedCondition)) {
            $this->resolvedCondition = isset($this->condition) ? $this->parseConditionalLogic(
                $this->condition()
            ) : true;
        }

        return $this->resolvedCondition;
    }

    /**
     * @return boolean|string
     */
    public function condition()
    {
        return $this->condition;
    }

    /**
     * @param boolean|string $condition A condition to evaluate.
     * @throws InvalidArgumentException If the condition is not a string nor boolean.
     * @return self
     */
    public function setCondition($condition)
    {
        if (!is_bool($condition) && !is_string($condition)) {
            throw new InvalidArgumentException(
                'Condition must be a string or boolean'
            );
        }

        $this->resolvedCondition = null;
        $this->condition = $condition;
        return $this;
    }

    /**
     * Resolve the conditional logic.
     *
     * @param  mixed $condition The condition.
     * @return boolean|null
     */
    final protected function parseConditionalLogic($condition)
    {
        if ($condition === null) {
            return null;
        }

        if (is_bool($condition)) {
            return $condition;
        }

        $not = false;
        if (is_string($condition)) {
            $not = ($condition[0] === '!');
            if ($not) {
                $condition = ltrim($condition, '!');
            }
        }

        $result = $this->resolveConditionalLogic($condition);

        return (($not) ? !$result : $result);
    }

    /**
     * Parse the widget's conditional logic.
     *
     * @todo Simplify logic by moving `form()` method lookup to relevant form widget.
     *
     * @param  callable|string $condition The callable or renderable condition.
     */
    protected function resolveConditionalLogic($condition): bool
    {
        if (is_callable([ $this, $condition ])) {
            return (bool) $this->{$condition}();
        }

        if (is_callable($condition)) {
            return (bool) $condition();
        }

        if (is_callable([ $this, 'form' ])) {
            $form = $this->form();

            if (is_callable([ $form, $condition ])) {
                return (bool) $form->{$condition}();
            }

            if (is_callable([ $form, 'obj' ])) {
                $obj = $form->obj();

                if (is_callable([ $obj, $condition ])) {
                    return (bool) $obj->{$condition}();
                }

                if (($obj instanceof ViewableInterface) && ($obj->view() instanceof ViewInterface)) {
                    return (bool) $obj->renderTemplate($condition);
                }
            }
        }

        return (bool) $condition;
    }
}
