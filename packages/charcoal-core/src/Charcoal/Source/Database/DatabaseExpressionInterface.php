<?php

namespace Charcoal\Source\Database;

/**
 * Describes a SQL-aware expression.
 */
interface DatabaseExpressionInterface
{
    /**
     * Converts the expression into a SQL string fragment.
     *
     * @return string A SQL fragment.
     */
    public function sql();
}
