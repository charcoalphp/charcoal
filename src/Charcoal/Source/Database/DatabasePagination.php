<?php

namespace Charcoal\Source\Database;

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

        $sql = $this->string();
        if ($sql) {
            return strtr($sql, [
                '{offset}' => $this->offset(),
                '{limit}'  => $this->limit(),
                '{page}'   => $this->page(),
            ]);
        }

        $sql   = [];
        $limit = $this->limit();
        if ($limit > 0) {
            $sql[] = 'LIMIT '.$limit;
        }

        $offset = $this->offset();
        if ($offset > 0) {
            $sql[] = 'OFFSET '.$offset;
        }

        return implode(' ', $sql);
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
