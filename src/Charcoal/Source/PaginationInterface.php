<?php

namespace Charcoal\Source;

/**
 * Defines a Limit/Offset Clause.
 */
interface PaginationInterface
{
    /**
     * Set the page number.
     *
     * @param  integer $page The current page. Starts at 0.
     * @throws InvalidArgumentException If the parameter is not numeric or < 0.
     * @return PaginationInterface Chainable
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
     * @param  integer $num The number ot item to retrieve per page.
     * @throws InvalidArgumentException If the parameter is not numeric or < 0.
     * @return PaginationInterface Chainable
     */
    public function setNumPerPage($num);

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
