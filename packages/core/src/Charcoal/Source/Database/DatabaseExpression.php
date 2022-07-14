<?php

namespace Charcoal\Source\Database;

use UnexpectedValueException;
// From 'charcoal-core'
use Charcoal\Source\Database\DatabaseExpressionInterface;
use Charcoal\Source\Expression;

/**
 * The DatabaseFilter makes a Filter SQL-aware.
 */
class DatabaseExpression extends Expression implements
    DatabaseExpressionInterface
{
    /**
     * Retrieve the Filter's SQL as a string to append to a WHERE clause.
     *
     * @return string
     */
    public function sql()
    {
        if ($this->active() && $this->hasCondition()) {
            return $this->byCondition();
        }

        return '';
    }

    /**
     * Retrieve the custom expression.
     *
     * @throws UnexpectedValueException If the custom expression is empty.
     * @return string
     */
    protected function byCondition()
    {
        if (!$this->hasCondition()) {
            throw new UnexpectedValueException(
                'Custom expression can not be empty.'
            );
        }

        return $this->condition();
    }
}
