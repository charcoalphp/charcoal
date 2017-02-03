<?php

namespace Charcoal\Source;

/**
 * Pagination Interface.
 */
interface PaginationInterface
{
    /**
     * @param array $data The pagination data (page, num_per_page).
     * @return Pagination Chainable
     */
    public function setData(array $data);

    /**
     * @param integer $page The current page. Starts at 0.
     * @return Pagination (Chainable)
     */
    public function setPage($page);

    /**
     * @return integer
     */
    public function page();

    /**
     * @param integer $num The number ot item to retrieve per page.
     * @throws InvalidArgumentException If the parameter is not numeric or < 0.
     * @return Pagination (Chainable)
     */
    public function setNumPerPage($num);
    /**
     * @return integer
     */
    public function numPerPage();

    /**
     * @return integer
     */
    public function first();

    /**
     * Can be greater than the actual number of items to retrieve.
     * @return integer
     */
    public function last();
}
