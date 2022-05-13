<?php

namespace Charcoal\Source;

/**
 * Describes a limit/offset clause of a query.
 */
interface PaginationInterface
{
    /**
     * Set the page number.
     *
     * @param  integer $page The current page.
     *     Pages should start at 1.
     * @throws \InvalidArgumentException If the parameter is not numeric or < 0.
     * @return PaginationInterface Returns the current expression.
     */
    public function setPage($page);

    /**
     * Retrieve the page number.
     *
     * @return integer
     */
    public function page();

    /**
     * Set the number of results per page.
     *
     * @param  integer $count The number of results to return, per page.
     *     Use 0 to request all results.
     * @throws \InvalidArgumentException If the parameter is not numeric or < 0.
     * @return PaginationInterface Returns the current expression.
     */
    public function setNumPerPage($count);

    /**
     * Retrieve the number of results per page.
     *
     * @return integer
     */
    public function numPerPage();

    /**
     * Retrieve the pagination's lowest possible index.
     *
     * @return integer
     */
    public function first();

    /**
     * Retrieve the pagination's highest possible index.
     *
     * @return integer
     */
    public function last();
}
