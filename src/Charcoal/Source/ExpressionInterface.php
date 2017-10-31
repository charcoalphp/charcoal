<?php

namespace Charcoal\Source;

use JsonSerializable;
use Serializable;

/**
 * Defines a statement expression.
 */
interface ExpressionInterface extends
    JsonSerializable,
    Serializable
{
    /**
     * Set the expression data.
     *
     * @param  array $data The expression data.
     * @return ExpressionInterface Chainable
     */
    public function setData(array $data);

    /**
     * Retrieve the expression data structure.
     *
     * @return array
     */
    public function data();

    /**
     * Set the custom expression.
     *
     * @param  string $expr The custom expression.
     * @throws InvalidArgumentException If the parameter is not a valid expression.
     * @return ExpressionInterface Chainable
     */
    public function setCondition($expr);

    /**
     * Retrieve the custom expression.
     *
     * @return string|null A custom expression.
     */
    public function condition();

    /**
     * Set the expression name.
     *
     * @param  string $name A unique identifier.
     * @throws InvalidArgumentException If the expression name invalid.
     * @return ExpressionInterface Chainable
     */
    public function setName($name);

    /**
     * Retrieve the expression name.
     *
     * @return string|null
     */
    public function name();

    /**
     * Set whether the expression is active or not.
     *
     * @param  boolean $active The active flag.
     * @return ExpressionInterface Chainable
     */
    public function setActive($active);

    /**
     * Determine if the expression is active or not.
     *
     * @return boolean TRUE if active, FALSE is disabled.
     */
    public function active();
}
