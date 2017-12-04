<?php

namespace Charcoal\Source;

use JsonSerializable;
use Serializable;

/**
 * Describes a query expression.
 */
interface ExpressionInterface extends
    JsonSerializable,
    Serializable
{
    /**
     * Set the expression data.
     *
     * @param  array<string,mixed> $data The expression data;
     *     as an associative array.
     * @return ExpressionInterface Returns the current expression.
     */
    public function setData(array $data);

    /**
     * Retrieve the expression data structure.
     *
     * @return array<string,mixed> An associative array.
     */
    public function data();

    /**
     * Set the expression name.
     *
     * @param  string $name A unique identifier.
     * @throws \InvalidArgumentException If the expression name invalid.
     * @return ExpressionInterface Returns the current expression.
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
     * @return ExpressionInterface Returns the current expression.
     */
    public function setActive($active);

    /**
     * Determine if the expression is active or not.
     *
     * @return boolean TRUE if active, FALSE is disabled.
     */
    public function active();
}
