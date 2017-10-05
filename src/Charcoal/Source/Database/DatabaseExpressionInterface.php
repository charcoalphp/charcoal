<?php

namespace Charcoal\Source\Database;

/**
 * Defines an SQL statement expression.
 */
interface DatabaseExpressionInterface
{
    /**
     * Retrieve the expression's SQL as a string.
     *
     * @return string
     */
    public function sql();
}
