<?php

namespace Charcoal\Ui;

use InvalidArgumentException;

/**
 * Provides an entity with a condition.
 *
 * Implementation of {@see \Charcoal\Ui\ConditionalizableInterface}
 */
interface ConditionalizableInterface
{
    /**
     * @return boolean
     */
    public function resolvedCondition();

    /**
     * @return boolean|string
     */
    public function condition();

    /**
     * @param boolean|string $condition A condition to evaluate.
     * @throws InvalidArgumentException If the condition is not a string nor boolean.
     * @return \Charcoal\Ui\ConditionalizableTrait
     */
    public function setCondition($condition);
}
