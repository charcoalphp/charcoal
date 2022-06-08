<?php

namespace Charcoal\Source;

use InvalidArgumentException;

/**
 * Describes a custom condition property.
 */
interface RawExpressionInterface
{
    /**
     * Set the custom query expression.
     *
     * @param  string|null $condition The custom query expression.
     * @throws InvalidArgumentException If the parameter is not a valid string expression.
     * @return self
     */
    public function setCondition($condition);

    /**
     * Determine if the expression has a custom condition.
     *
     * @return boolean
     */
    public function hasCondition();

    /**
     * Retrieve the custom query expression.
     *
     * @return mixed
     */
    public function condition();
}
