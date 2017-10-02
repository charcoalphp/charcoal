<?php

namespace Charcoal\Source\Database;

use UnexpectedValueException;

// From 'charcoal-core'
use Charcoal\Source\Pagination;

/**
 * The DatabasePagination makes a Pagination SQL-aware
 */
class DatabasePagination extends Pagination
{
    /**
     * Generate the pagination's SQL string (full "LIMIT" clause)
     *
     * For example, for the pagination `{ page: 3, num_per_page: 50 }` the result
     * would be: `' LIMIT 100, 50'`.
     *
     * @return string
     */
    public function sql()
    {
        if ($this->active() === false) {
            return '';
        }

        if ($this->hasString()) {
            return $this->byCondition();
        }

        if ($this->hasLimit()) {
            return $this->byLimit();
        }

        return '';
    }

    /**
     * Retrieve the custom LIMIT clause.
     *
     * @throws UnexpectedValueException If the custom clause is empty.
     * @return string
     */
    protected function byCondition()
    {
        if (!$this->hasString()) {
            throw new UnexpectedValueException(
                'Custom expression can not be empty.'
            );
        }

        return $this->string();
    }

    /**
     * Retrieve the LIMIT clause by page number and results per page.
     *
     * @throws UnexpectedValueException If there is no count per page.
     * @return string
     */
    protected function byLimit()
    {
        $limit = $this->limit();
        if ($limit < 1) {
            throw new UnexpectedValueException(
                'Number Per Page must be greater than zero.'
            );
        }

        $offset = $this->offset();
        return 'LIMIT '.$offset.', '.$limit;
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
