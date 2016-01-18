<?php

namespace Charcoal\Source;

/**
* Pagination Interface.
*/
interface PaginationInterface
{
    /**
    * @param array $data
    * @return Pagination Chainable
    */
    public function setData(array $data);

    /**
    * @param integer $page
    * @throws InvalidArgumentException if the parameter is not numeric or < 0
    * @return Pagination (Chainable)
    */
    public function setPage($page);

    /**
    * @return integer
    */
    public function page();

    /**
    * @param integer $num
    * @throws InvalidArgumentException if the parameter is not numeric or < 0
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
    * Can be greater than the actual number of items to retrieve
    * @return integer
    */
    public function last();
}
