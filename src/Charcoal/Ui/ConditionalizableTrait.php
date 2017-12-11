<?php

namespace Charcoal\Ui;

use InvalidArgumentException;

/**
 * Provides an entity with a condition.
 *
 * Implementation of {@see \Charcoal\Ui\ConditionalizableInterface}
 */
trait ConditionalizableTrait
{
    /**
     * Condition needed to render the entity.
     *
     * @var string|boolean
     */
    private $condition;

    /**
     * rendered condition.
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
            if (!isset($this->condition)) {
                $this->resolvedCondition = true;
            } else {
                $this->resolvedCondition = $this->parseConditionalLogic(
                    $this->condition()
                );
            }
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

        unset($this->resolvedCondition);
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

        return (($not === true) ? !$result : $result);
    }

    /**
     * Parse the widget's conditional logic.
     *
     * @param  callable|string $condition The callable or renderable condition.
     * @return boolean
     */
    protected function resolveConditionalLogic($condition)
    {
        if (is_callable([ $this, $condition ])) {
            return !!$this->{$condition}();
        } elseif (is_callable($condition)) {
            return !!$condition();
        } elseif ($this->form()->obj()->view()) {
            return !!$this->form()->obj()->renderTemplate($condition);
        }

        return !!$condition;
    }
}
