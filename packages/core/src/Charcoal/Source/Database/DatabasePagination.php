<?php

namespace Charcoal\Source\Database;

use UnexpectedValueException;
// From 'charcoal-core'
use Charcoal\Source\Database\DatabaseExpressionInterface;
use Charcoal\Source\Pagination;

/**
 * SQL Pagination Clause
 */
class DatabasePagination extends Pagination implements
    DatabaseExpressionInterface
{
    /**
     * Converts the pagination into a SQL expression for the LIMIT clause.
     *
     * @return string A SQL string fragment.
     */
    public function sql()
    {
        if ($this->active() && $this->hasLimit()) {
            $limit  = $this->limit();
            $offset = $this->offset();
            return 'LIMIT ' . $offset . ', ' . $limit;
        }

        return '';
    }

    /**
     * Determine if the expression has a number per page.
     *
     * @return boolean
     */
    public function hasLimit()
    {
        return ($this->limit() > 0);
    }

    /**
     * Alias of {@see self::numPerPage()}
     *
     * @return integer
     */
    public function limit()
    {
        return $this->numPerPage();
    }

    /**
     * Retrieve the offset from the page number and count.
     *
     * @return integer
     */
    public function offset()
    {
        $page   = $this->page();
        $limit  = $this->numPerPage();
        $offset = (($page - 1) * $limit);
        if (PHP_INT_MAX <= $offset) {
            $offset = PHP_INT_MAX;
        }

        return max(0, $offset);
    }
}
