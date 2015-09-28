<?php

namespace Charcoal\Source\Database;

// Local parent namespace dependencies
use \Charcoal\Source\Pagination;

/**
* The DatabasePagination makes a Pagination SQL-aware
*/
class DatabasePagination extends Pagination
{
    /**
    * Get the pagination's SQL string (Full "LIMIT" subquery)
    *
    * @return string
    */
    public function sql()
    {
        $sql = '';
        $page = $this->page();
        $num_per_page = $this->num_per_page();

        if ($page && $num_per_page) {
            $first_page = max(0, (($page-1)*$num_per_page));
            $sql = ' LIMIT '.$first_page.', '.$num_per_page;
        }
        return $sql;
    }
}
