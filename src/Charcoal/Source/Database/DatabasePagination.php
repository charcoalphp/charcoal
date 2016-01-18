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
        $numPerPage = $this->numPerPage();

        if ($page && $numPerPage) {
            $first_page = max(0, (($page-1)*$numPerPage));
            $sql = ' LIMIT '.$first_page.', '.$numPerPage;
        }
        return $sql;
    }
}
